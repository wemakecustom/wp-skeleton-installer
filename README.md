Wordpress Skeleton Installer
==================

Will be installed with [wp-skeleton][1].


## What it does

Install WordPress and dependencies:
* [wemakecustom/composer-script-utils][2] (version ^1.0)
* [wp-cli/wp-cli][3]
* [johnpbloch/wordpress][4] (version ^4.0)
* [wemakecustom/wp-pot-generator][5] (version ~2.0@dev)

Add all default folders and files needed:
* create plugins, mu-plugins, themes folders into ``/htdocs/wp-content/``
* add the default ``.gitignore`` for wordpress.
* executed scripts before and after each update/install with composer to ensure a stable environement.

[1]: https://github.com/wemakecustom/wp-skeleton
[2]: https://github.com/wemakecustom/composer-script-utils
[3]: https://github.com/wp-cli/wp-cli
[4]: https://github.com/johnpbloch/wordpress
[5]: https://github.com/wemakecustom/wp-pot-generator


## Installation

````
$ git clone git@github.com:wemakecustom/wp-skeleton-installer.git <project>
$ cd <project>
$ composer install
````

You will see prompt messages to set default database information, wordpress language and debug parameters.

``ScriptHandler`` manage the installation:
* install must-use plugins;
* move and create symlink to WordPress files and folders;
* manage config's files (database and basics configuration needed for WordPress);
* set SALT keys for encryption of information stored in the user's cookies

By default, at the end, you will get WordPress and all configs files installed in ``<project>/test/`` subfolder. To configure another subfolder, please see section _configuration_ bellow.


## Configuration

The following section in ``composer.json`` allow you to change the folder where WordPress and the config files are installed.
For an example to see how to change this section, please see [wp-skeleton][1].

```json
{
    "extra": {
        "wordpress-install-dir": "vendor/wordpress/wordpress",
        "confs-dir": "test/confs",
        "web-dir": "test/htdocs",
        "installer-paths": {
            "test/htdocs/wp-content/plugins/{$name}/": ["type:wordpress-plugin"],
            "test/htdocs/wp-content/mu-plugins/{$name}/": ["type:wordpress-muplugin"],
            "test/htdocs/wp-content/themes/composer/{$name}/": ["type:wordpress-theme"]
        }
    }
}
```

So, you can change:
* ``wordpress-install-dir`` to specify where you want to install WordPress core.
* ``confs-dir`` to define where you install config's files _(ie database)_.
* ``web-dir`` to set the subfolder for WordPress, where core symlinks, themes and plugins will be installed. **You will work in this subfolder**.
