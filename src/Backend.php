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
use Dotclear\Helper\Process\TraitProcess;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('pingMastodon');
        __('Ping Mastodon');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem(App::backend()->menus()::MENU_BLOG);

        $settings = My::settings();
        // Add posts/pages action
        if ($settings->active && App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            App::behavior()->addBehaviors([
                /* Add behavior callbacks for posts actions */
                'adminPostsActions' => BackendBehaviors::adminPostsActions(...),
                'adminPagesActions' => BackendBehaviors::adminPagesActions(...),
            ]);
        }

        App::behavior()->addBehaviors([
            /* Add behavior callbacks for managing catchphrase */
            'adminPostHeaders'     => fn (): string => My::jsLoad('post') . My::cssLoad('style'),
            'adminPostFormItems'   => BackendBehaviors::adminPostFormItems(...),
            'adminAfterPostCreate' => BackendBehaviors::setCatchPhrase(...),
            'adminAfterPostUpdate' => BackendBehaviors::setCatchPhrase(...),
        ]);

        return true;
    }
}
