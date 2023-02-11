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

// pingMastodon behavior
dcCore::app()->addBehavior('coreFirstPublicationEntries', function (dcBlog $blog, array $ids) {
    // Needed until 2.25
    $blog->settings->addNamespace('pingMastodon');

    // Check plugin activation for the current blog
    if (!$blog->settings->pingMastodon->active) {
        return;
    }

    $instance = $blog->settings->pingMastodon->instance;
    $token    = $blog->settings->pingMastodon->token;
    $prefix   = $blog->settings->pingMastodon->prefix;

    if ($prefix !== '') {
        $prefix .= ' ';
    }

    if (empty($instance) || empty($token) || count($ids) === 0) {
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
            netHttp::quickPost($uri, $payload);
        }
    } catch (Exception $e) {
    }
});
