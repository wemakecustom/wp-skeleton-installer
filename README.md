Wordpress Skeleton Installer
==================

Install scripts to work with [wp-skeleton](https://github.com/wemakecustom/wp-skeleton).

## What it does

- install _WordPress_ with composer :
  * johnpbloch/wordpress (version ^4.0)
  * wemakecustom/wp-pot-generator (version ~2.0@dev)
  * wemakecustom/composer-script-utils
  * wp-cli/wp-cli
- create plugins, mu-plugins, themes folders into _/htdocs/wp-content/_
- add the default .gitignore for wordpress.
- executed scripts before and after each update/install with composer to ensure a stable environement.
- add new binaries dependencies :
  * bin/rename-prefix.php _(help to change wordpress tables prefix)_
  * bin/update-wpskeleton.php

## Installation

Clone this git repo.
````
$ git clone git@github.com:wemakecustom/wp-skeleton-installer.git
````
Run composer to install.
````
$ composer install
````
You will see a prompt message to set default database information, WPLANG and WP_DEBUG.

## Scripts executed for each update and install

* ScriptHandler::installMuRequire _(install all must-use plugins)_
* ScriptHandler::rsync _(move files from temporary to destination folder)_
* ScriptHandler::verifyGitignore _(install default .gitignore)_
* ScriptHandler::installWpConfig _(install default .wp-config.php)_
* ScriptHandler::configureAbspath _(set the ABSPath for /htdocs/)_
* ScriptHandler::updateConfigs _(update config's files with database and basics configuration needed for WordPress)_
* ScriptHandler::wordpressSymlinks _(create symlink to WordPress files (except for license.txt, readme.html, composer.json, wp-config-sample.php, wp-config.php, /wp-content/)_
* ScriptHandler::generateRandomKeys _(set random variables for encryption of information stored in the user's cookies)_

## Configuration

```json
{
    "require": {
        "wemakecustom/wp-skeleton-installer": "*",
    },
    "scripts": {
        "post-install-cmd": [
            "WMC\\Wordpress\\SkeletonInstaller\\Composer\\ScriptHandler::handle",
        ],
        "post-update-cmd": [
            "WMC\\Wordpress\\SkeletonInstaller\\Composer\\ScriptHandler::handle",
        ]
    },
    "extra": {
        "wordpress-install-dir": "vendor/wordpress/wordpress",
    }
}
```

## Installation
- https://github.com/wemakecustom/wp-skeleton
- https://github.com/johnpbloch/wordpress
- https://github.com/wemakecustom/wp-mu-loader
- https://github.com/wemakecustom/wp-pot-generator
- https://github.com/wp-cli/wp-cli
- https://github.com/wemakecustom/composer-script-utils
