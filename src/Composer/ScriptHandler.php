<?php

namespace WMC\Wordpress\SkeletonInstaller\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Composer;
use WMC\Composer\Utils\Filesystem\PathUtil;
use WMC\Composer\Utils\Composer\PackageLocator;
use WMC\Composer\Utils\ConfigFile\ConfigDir;

class ScriptHandler
{
    protected static $wpPackage = 'johnpbloch/wordpress';

    public static function handle(Event $event)
    {
        self::wordpressTweaks($event);
        self::wordpressSymlinks($event);
        self::generateRandomKeys($event);
    }

    /**
     * @deprecated should be private
     */
    public static function wordpressSymlinks(Event $event)
    {
        $composer = $event->getComposer();
        $io       = $event->getIO();
        $extras   = $composer->getPackage()->getExtra();
        $web_dir  = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);
        $wp_dir   = PackageLocator::getPackagePath($event->getComposer(), static::$wpPackage);

        $io->write(sprintf(
            'Symlinking <info>%s/*</info> into <info>%s/</info>.',
            str_replace(getcwd().'/', '', $wp_dir),
            str_replace(getcwd().'/', '', $web_dir)
        ));

        $symlink = function ($link_dir, $target_dir, $file, $target_file = null) use ($io) {
            if (null === $target_file) {
                $target_file = $file;
            }

            $link   = "$link_dir/$file";
            $target = PathUtil::getRelativePath($link_dir, $target_dir) . ($target_file ? '/'.$target_file : '');

            if (@readlink($link)) {
                if (readlink($link) === $target) {
                    return;
                } else {
                    unlink($link);
                }
            }

            if (file_exists($link)) {
                $io->write(sprintf('<error>Error while creating a symlink to %s: file exists</error>', str_replace(getcwd().'/', '', $link)));
            } else {
                $io->write(sprintf('Creating symlink <info>%s</info> -> <info>%s</info>.', str_replace(getcwd().'/', '', $link), $target));
                symlink($target, $link);
            }
        };

        foreach (scandir($wp_dir) as $file) {
            if ($file[0] == '.') continue;

            switch ($file) {
                case 'license.txt':
                case 'readme.html':
                case 'composer.json':
                case 'wp-config-sample.php':
                case 'wp-config.php':
                case 'wp-content':
                    break;
                default:
                    $symlink($web_dir, $wp_dir, $file);
                    break;
            }
        }

        // Link core themes
        $symlink("$web_dir/wp-content/themes", "$wp_dir/wp-content/themes", "default-themes", "");

        // Link our wp-config back into wordpress files
        $symlink($wp_dir, $web_dir, "wp-config.php");
    }

    /**
     * @deprecated should be private
     */
    public static function wordpressTweaks(Event $event)
    {
        $composer  = $event->getComposer();
        $io        = $event->getIO();
        $extras    = $composer->getPackage()->getExtra();
        $web_dir   = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);
        $confs_dir = getcwd() . '/' . (empty($extras['confs-dir']) ? 'confs' : $extras['confs-dir']);

        self::installMuRequire($io, $composer);
        self::rsync($io, dirname(dirname(__DIR__)) . '/dist/confs', $confs_dir);
        self::rsync($io, dirname(dirname(__DIR__)) . '/dist/htdocs', $web_dir);
        self::verifyGitignore($io, dirname($web_dir) . '/.gitignore');
        self::installWpConfig($io, $web_dir);
        self::configureAbspath($io, $composer, $web_dir);
        self::updateConfigs($io, $confs_dir);
    }

    private static function updateConfigs(IOInterface $io, $confs_dir)
    {
        $configDir = new ConfigDir($io);
        $configDir->updateDir($confs_dir, "${confs_dir}/samples");
    }

    private static function installMuRequire(IOInterface $io, $composer)
    {
        $mu_loader = PackageLocator::getPackagePath($composer, 'wemakecustom/wp-mu-loader');
        if ($mu_loader) {
            if (!file_exists(dirname($mu_loader) . '/mu-require.php')
                || md5_file("$mu_loader/mu-require.php") != md5_file(dirname($mu_loader) . '/mu-require.php')) {
                $io->write('Installing <info>mu-require.php</info>');
                copy("$mu_loader/mu-require.php", dirname($mu_loader) . '/mu-require.php');
            }
        }
    }

    private static function installWpConfig(IOInterface $io, $web_dir)
    {
        if (!file_exists("${web_dir}/wp-config.php")) {
            $io->write('Installing default <info>wp-config.php</info>.');
            copy("${web_dir}/wp-config-sample.php", "${web_dir}/wp-config.php");
        }

        // Old version of wp-config did not require wp-settings. wp-cli needs it, so automatically add it
        if (!preg_match('/^\s*require.+wp-settings\.php/m', file_get_contents("${web_dir}/wp-config.php"))) {
            $io->write('Adding missing require wp-settings.php to <info>wp-config.php</info>.');
            file_put_contents("${web_dir}/wp-config.php", "\nrequire_once(__DIR__ . '/wp-settings.php');\n", FILE_APPEND);
        }
    }

    private static function configureAbspath(IOInterface $io, $composer, $web_dir)
    {
        $wp_dir   = PackageLocator::getPackagePath($composer, static::$wpPackage);
        $wp_load = "$wp_dir/wp-load.php";
        $abspath = $web_dir . '/';

        if (!is_file($wp_load)) {
            $io->write(sprintf(
                '<error>Wordpress installation is broken, %s is missing.</error>',
                str_replace(getcwd().'/', '', $wp_load)
            ));
        }

        $io->write(sprintf('Setting <info>ABSPATH</info> to <info>%s</info>.', $abspath));

        $define = "define( 'ABSPATH', '$abspath' );";
        file_put_contents($wp_load, preg_replace("/^define\(\s*'ABSPATH'.+$/m", $define, file_get_contents($wp_load)));
    }

    private static function rsync(IOInterface $io, $source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        foreach (scandir($source) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir("${source}/${file}")) {
                self::rsync($io, "${source}/${file}", "${destination}/${file}");
            } elseif (!is_file("${destination}/${file}") || md5_file("${destination}/${file}") !== md5_file("${source}/${file}")) {
                $io->write(sprintf(
                    'Installing file <info>%s</info>.',
                    str_replace(getcwd().'/', '', "${destination}/${file}")
                ));
                copy("${source}/${file}", "${destination}/${file}");
            }
        }
    }

    /**
     * Merges the dist gitignore to the project’s one
     *
     * @param  IOInterface $io
     * @param  string $destination Absolute path to target .gitignore
     */
    private static function verifyGitignore(IOInterface $io, $destination)
    {
        $source = dirname(dirname(__DIR__)) . '/dist/gitignore';

        if (is_file($destination)) {
            // do not consider commented or empty lines
            $parseLines = function($file) {
                $lines = file($file);
                $lines = array_filter($lines, function($line) { return !preg_match('/^(#|\s*$)/', $line); });
                $lines = array_map('trim', $lines);

                return $lines;
            };
            $lines     = $parseLines($source);
            $existings = $parseLines($destination);
            $missings  = array_diff($lines, $existings);

            if (!empty($missings)) {
                $io->write(sprintf(
                    '<info>%s</info> is missing those lines:',
                    str_replace(getcwd().'/', '', $destination)
                ));
                foreach ($missings as $missing) {
                    $io->write('  • ' . $missing);
                }
                if ($io->askConfirmation('Would you like to add them ? (y/N) ', false)) {
                    $io->write(sprintf('Adding <info>%s</info> lines to gitignore.', count($missings)));
                    file_put_contents($destination, PHP_EOL . implode(PHP_EOL, $missings) . PHP_EOL, FILE_APPEND);
                }
            }
        } else {
            // do not prompt, add file automatically
            $io->write(sprintf(
                'Installing default <info>%s</info>.',
                str_replace(getcwd().'/', '', $destination)
            ));
            copy($source, $destination);
        }
    }

    /**
     * @deprecated should be private
     */
    public static function generateRandomKeys(Event $event)
    {
        $composer = $event->getComposer();
        $io       = $event->getIO();
        $extras   = $composer->getPackage()->getExtra();
        $web_dir  = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);

        $file = "$web_dir/random-keys.php";

        if (!is_file($file)) {
            if (is_writable(dirname($file))) {
                $api = 'https://api.wordpress.org/secret-key/1.1/salt/';
                $io->write(sprintf('Generating secret keys using <info>%s</info>', $api));

                $rnd = "<?php\n\n" . file_get_contents($api);
                file_put_contents($file, $rnd);
            } else {
                $io->write('<error>Error while generating secret keys: random-keys.php is not writable</error>');
            }
        }
    }
}
