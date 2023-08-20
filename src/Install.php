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

use dcCore;
use dcNamespace;
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
            $settings = dcCore::app()->blog->settings->get(My::id());

            $settings->put('active', false, dcNamespace::NS_BOOL, 'Active', false, true);

            $settings->put('instance', '', dcNamespace::NS_STRING, 'Instance URL', false, true);
            $settings->put('token', '', dcNamespace::NS_STRING, 'App token', false, true);
            $settings->put('prefix', '', dcNamespace::NS_STRING, 'Status prefix', false, true);
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
