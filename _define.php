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
    'Ping Mastodon',
    'Ping Mastodon',
    'Franck Paul',
    '5.2.1',
    [
        'date'        => '2025-05-22T16:39:45+0200',
        'requires'    => [['core', '2.33']],
        'type'        => 'plugin',
        'permissions' => 'My',
        'details'     => 'https://open-time.net/docs/plugins/pingMastodon',
        'support'     => 'https://github.com/franck-paul/pingMastodon',
        'repository'  => 'https://raw.githubusercontent.com/franck-paul/pingMastodon/main/dcstore.xml',
        'license'     => 'gpl2',
    ]
);
