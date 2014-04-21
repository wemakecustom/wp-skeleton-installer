WP Skeleton Installer
======================

Install scripts intented to work with https://github.com/wemakecustom/wp-skeleton

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
