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

// dead but useful code, in order to have translations
__('pingMastodon') . __('Ping Mastodon');

dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Ping Mastodon'),
    dcCore::app()->adminurl->get('admin.plugin.pingMastodon'),
    [urldecode(dcPage::getPF('pingMastodon/icon.svg')), urldecode(dcPage::getPF('pingMastodon/icon-dark.svg'))],
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.pingMastodon')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
        dcAuth::PERMISSION_ADMIN,
    ]), dcCore::app()->blog->id)
);
