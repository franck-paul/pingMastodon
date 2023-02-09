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
    }

    /**
     * Processes the request(s).
     */
    public static function process()
    {
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
                html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Ping Mastodon')                         => '',
            ]
        ) .
        dcPage::notices();

        echo '<p>' . __('Current version of Dotclear:') . ' <strong>' . DC_VERSION . '</strong></p>';

        echo
        '</body>' .
        '</html>';
    }
}

adminPingMastodon::init();
adminPingMastodon::process();
adminPingMastodon::render();
