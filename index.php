<?php
/**
 * @brief pingMastodon, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;

if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

class adminPingMastodon
{
    /**
     * Initializes the page.
     */
    public static function init()
    {
        dcCore::app()->blog->settings->addNamespace('pingMastodon');    // Needed until 2.25

        dcCore::app()->admin->active = (bool) dcCore::app()->blog->settings->pingMastodon->active;

        dcCore::app()->admin->instance = (string) dcCore::app()->blog->settings->pingMastodon->instance;
        dcCore::app()->admin->token    = (string) dcCore::app()->blog->settings->pingMastodon->token;
        dcCore::app()->admin->prefix   = (string) dcCore::app()->blog->settings->pingMastodon->prefix;
    }

    /**
     * Processes the request(s).
     */
    public static function process()
    {
        try {
            if (!empty($_POST)) {
                dcCore::app()->blog->settings->addNamespace('pingMastodon');    // Needed until 2.25

                dcCore::app()->blog->settings->pingMastodon->put('active', !empty($_POST['pm_active']));

                dcCore::app()->blog->settings->pingMastodon->put('instance', trim(Html::escapeHTML($_POST['pm_instance'])));
                dcCore::app()->blog->settings->pingMastodon->put('token', trim(Html::escapeHTML($_POST['pm_token'])));
                dcCore::app()->blog->settings->pingMastodon->put('prefix', trim(Html::escapeHTML($_POST['pm_prefix'])));

                dcCore::app()->blog->triggerBlog();

                dcPage::addSuccessNotice(__('Settings have been successfully updated.'));
                Http::redirect(dcCore::app()->admin->getPageURL());
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    /**
     * Renders the page.
     */
    public static function render()
    {
        echo
        '<html>' .
        '<head>' .
        '<title>' . __('Ping Mastodon') . '</title>';

        echo
        '</head>' .
        '<body>' .
        dcPage::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Ping Mastodon')                         => '',
            ]
        ) .
        dcPage::notices();

        echo
        '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
        '<p>' . form::checkbox('pm_active', 1, dcCore::app()->admin->active) . ' ' .
        '<label for="pm_active" class="classic">' . __('Activate pingMastodon plugin') . '</label></p>' .

        '<p><label for="pm_instance">' . __('Mastodon instance:') . '</label> ' .
        form::field('pm_instance', 48, 128, Html::escapeHTML(dcCore::app()->admin->instance)) . '</p>' .
        '<p><label for="pm_token">' . __('Application token:') . '</label> ' .
        form::field('pm_token', 64, 128, Html::escapeHTML(dcCore::app()->admin->token)) . '</p>' .
        '<p><label for="pm_prefix">' . __('Status prefix:') . '</label> ' .
        form::field('pm_prefix', 30, 128, Html::escapeHTML(dcCore::app()->admin->prefix)) . '</p>' .

        '<p>' . dcCore::app()->formNonce() . '<input type="submit" value="' . __('Save') . '" /></p>' .
        '</form>';

        echo
        '</body>' .
        '</html>';
    }
}

adminPingMastodon::init();
adminPingMastodon::process();
adminPingMastodon::render();
