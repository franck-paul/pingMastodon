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

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

try {
    dcCore::app()->blog->settings->addNamespace('pingMastodon');

    dcCore::app()->blog->settings->pingMastodon->put('active', false, 'boolean', 'Active', false, true);

    dcCore::app()->blog->settings->pingMastodon->put('instance', '', 'string', 'Instance URL', false, true);
    dcCore::app()->blog->settings->pingMastodon->put('token', '', 'string', 'App token', false, true);
    dcCore::app()->blog->settings->pingMastodon->put('prefix', '', 'string', 'Status prefix', false, true);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
