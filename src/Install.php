<?php
/**
 * @brief pingMastodon, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\pingMastodon;

use Dotclear\App;
use Dotclear\Core\Process;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            // Init
            $settings = My::settings();

            $settings->put('active', false, App::blogWorkspace()::NS_BOOL, 'Active', false, true);

            $settings->put('instance', '', App::blogWorkspace()::NS_STRING, 'Instance URL', false, true);
            $settings->put('token', '', App::blogWorkspace()::NS_STRING, 'App token', false, true);
            $settings->put('prefix', '', App::blogWorkspace()::NS_STRING, 'Status prefix', false, true);
            $settings->put('tags', false, App::blogWorkspace()::NS_BOOL, 'Include tags', false, true);
            $settings->put('tags_mode', My::REFS_MODE_CAMELCASE, App::blogWorkspace()::NS_INT, 'Tags conversion mode', false, true);
            $settings->put('cats', false, App::blogWorkspace()::NS_BOOL, 'Include categories as tag', false, true);
            $settings->put('cats_mode', My::REFS_MODE_CAMELCASE, App::blogWorkspace()::NS_INT, 'Categories conversion mode', false, true);
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        return true;
    }
}
