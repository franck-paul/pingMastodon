<?php

/**
 * @brief pingMastodon, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Ping Mastodon',
        'Ping Mastodon',
        'Franck Paul',
        '7.0.1',
        [
            'date'        => '2026-08-05T10:14:12+0200',
            'requires'    => [['core', '2.39']],
            'type'        => 'plugin',
            'permissions' => 'My',
            'details'     => 'https://open-time.net/docs/plugins/pingMastodon',
            'support'     => 'https://github.com/franck-paul/pingMastodon',
            'repository'  => 'https://raw.githubusercontent.com/franck-paul/pingMastodon/main/dcstore.xml',
            'license'     => 'gpl2',
        ]
    );
}
