<?php

namespace WMC\Wordpress\SkeletonInstaller\Composer;

use Composer\Script\Event;

class ConfHandler
{
    /**
     * @deprecated
     */
    public static function updateFiles(Event $event)
    {
        // handled by ScriptHandler::updateConfigs
    }
}
