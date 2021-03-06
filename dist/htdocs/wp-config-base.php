<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clefs secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/Editing_wp-config.php Modifier
 * wp-config.php} (en anglais). C'est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d'installation. Vous n'avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

$ini_file = dirname(dirname(__FILE__)) . '/confs/database.ini';

if (!is_file($ini_file)) {
    die('Please create a database configuration in ../confs/database.ini');
}
$database = parse_ini_file($ini_file);

define('DB_NAME', $database['name']);
define('DB_USER', $database['user']);
define('DB_PASSWORD', $database['pass']);
define('DB_HOST', $database['host']);
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


$ini_file = dirname(dirname(__FILE__)) . '/confs/wp-config.ini';
$config = array(
    'WPLANG'   => "en_US",
    'WP_DEBUG' => true,
);
if (is_file($ini_file)) {
    $config = array_merge($config, parse_ini_file($ini_file));
}

define('WPLANG', $config['WPLANG']);
define('WP_DEBUG', (bool) $config['WP_DEBUG']);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', true);


require 'random-keys.php';

if (isset($_SERVER['HTTP_HOST'])) {
    define('WP_SITEURL', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);
    define('WP_HOME', WP_SITEURL . '/');
}

/** Chemin absolu vers le dossier de WordPress. */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/** Disallow editing files plugins and themes **/
define('DISALLOW_FILE_EDIT', true);
