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

use dcBlog;
use Dotclear\Helper\Network\HttpClient;
use Exception;

class Helper
{
    public static function ping(dcBlog $blog, array $ids)
    {
        $settings = My::settings();
        if (!$settings->active) {
            return;
        }

        $instance = $settings->instance;
        $token    = $settings->token;
        $prefix   = $settings->prefix;

        if (!empty($prefix)) {
            $prefix .= ' ';
        }

        if (empty($instance) || empty($token) || empty($ids)) {
            return;
        }

        // Prepare instance URI
        if (!parse_url($instance, PHP_URL_HOST)) {
            $instance = 'https://' . $instance;
        }
        $uri = rtrim($instance, '/') . '/api/v1/statuses?access_token=' . $token;

        try {
            // Get posts information
            $rs = $blog->getPosts(['post_id' => $ids]);
            $rs->extend('rsExtPost');
            while ($rs->fetch()) {
                $payload = [
                    'status'     => $prefix . $rs->post_title . ' ' . $rs->getURL(),
                    'visibility' => 'public',       // public, unlisted, private, direct
                ];
                HttpClient::quickPost($uri, $payload);
            }
        } catch (Exception $e) {
        }
    }
}
