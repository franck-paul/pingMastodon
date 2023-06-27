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
$this->registerModule(
    'pingMastodon',
    'Ping Mastodon',
    'Franck Paul',
    '2.3.1',
    [
        'requires'    => [['core', '2.26']],
        'type'        => 'plugin',
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_ADMIN,
        ]),
        'details'    => 'https://open-time.net/docs/plugins/pingMastodon',
        'support'    => 'https://github.com/franck-paul/pingMastodon',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/pingMastodon/main/dcstore.xml',
    ]
);
