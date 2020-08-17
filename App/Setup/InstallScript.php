<?php
/**
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace App\Setup;

use Composer\Script\Event;

/**
 * Post-install class triggered during 'compser install' to set up system dependencies.
 *
 * @since  1.0
 */
class InstallScript
{
    public static function postInstall(Event $event)
    {
        // Check if a config.json file exists, copy the config.dist.json if not
        if (!file_exists('App/Config/config.json')) {
            copy('App/Config/config.dist.json', 'App/Config/config.json');
        }

        // Make sure the assets directory exists
        if (!is_dir('www/assets')) {
            mkdir('www/assets', 0755);
            mkdir('www/assets/js', 0755);
            mkdir('www/assets/css', 0755);
        }

        $vendorDir = __DIR__.'/../../';

        // copy js files
        $js = array(
            'vendor/twbs/bootstrap/dist/js/bootstrap.js',
            'vendor/twbs/bootstrap/dist/js/bootstrap.min.js',

            'vendor/components/jquery/jquery.js',
            'vendor/components/jquery/jquery.min.js',
        );

        $jsAssetsPath = 'www/assets/js';

        if (!is_dir($jsAssetsPath)) {
            mkdir($jsAssetsPath, 0755);
        }

        foreach ($js as $file) {
            copy($vendorDir.$file, $jsAssetsPath.'/'.basename($file));
        }

        // copy css files
        $css = array(
            'vendor/twbs/bootstrap/dist/css/bootstrap.css',
            'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            'vendor/twbs/bootstrap/dist/css/bootstrap-grid.css',
            'vendor/twbs/bootstrap/dist/css/bootstrap-grid.min.css',
            'vendor/twbs/bootstrap/dist/css/bootstrap-reboot.css',
            'vendor/twbs/bootstrap/dist/css/bootstrap-reboot.min.css',
        );

        $cssAssetsPath = 'www/assets/css';

        if (!is_dir($cssAssetsPath)) {
            mkdir($cssAssetsPath, 0755);
        }

        foreach ($css as $file) {
            copy($vendorDir.$file, $cssAssetsPath.'/'.basename($file));
        }
    }
}
