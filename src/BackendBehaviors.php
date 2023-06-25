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

use arrayObject;
use dcAuth;
use dcBlog;
use dcCore;
use dcPage;
use dcPostsActions;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    public static function adminPostsActions(dcPostsActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Mastodon') => [__('Ping Mastodon') => 'pingMastodon']],
                [self::class, 'adminPingMastodon']
            );
        }
    }

    public static function adminPagesActions(PagesBackendActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Mastodon') => [__('Ping Mastodon') => 'pingMastodon']],
                [self::class, 'adminPingMastodon']
            );
        }
    }

    public static function adminPingMastodon($ap, arrayObject $post)
    {
        $rs = $ap->getRS();
        if ($rs->rows()) {
            $ids = [];
            while ($rs->fetch()) {
                if ((int) $rs->post_status === dcBlog::POST_PUBLISHED) {
                    // Ping only published entry
                    $ids[] = $rs->post_id;
                }
            }
            if (count($ids)) {
                Helper::ping(dcCore::app()->blog, $ids);
                dcPage::addSuccessNotice(__('All entries have been ping to Mastodon.'));
            }
            $ap->redirect(true);
        } else {
            $ap->redirect();
        }
    }
}
