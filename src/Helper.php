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

use Dotclear\App;
use Dotclear\Helper\Network\HttpClient;
use Dotclear\Interface\Core\BlogInterface;
use Dotclear\Schema\Extension\Post;
use Exception;

class Helper
{
    /**
     * Ping mastodon server
     *
     * @param      BlogInterface        $blog   The blog
     * @param      array<int>           $ids    The identifiers
     */
    public static function ping(BlogInterface $blog, array $ids): string
    {
        $settings = My::settings();
        if (!$settings->active) {
            return '';
        }

        $instance = $settings->instance;
        $token    = $settings->token;
        $prefix   = $settings->prefix;
        $addtags  = $settings->tags;
        $mode     = $settings->tags_mode;

        if (empty($instance) || empty($token) || $ids === []) {
            return '';
        }

        // Prepare instance URI
        if (!parse_url($instance, PHP_URL_HOST)) {
            $instance = 'https://' . $instance;
        }

        $uri = rtrim($instance, '/') . '/api/v1/statuses?access_token=' . $token;

        try {
            // Get posts information
            $rs = $blog->getPosts(['post_id' => $ids]);
            $rs->extend(Post::class);
            while ($rs->fetch()) {
                $elements = [];
                // Prefix
                if (!empty($prefix)) {
                    $elements[] = $prefix;
                }
                // Title
                $elements[] = $rs->post_title;
                // Tags
                if ($addtags) {
                    $tags = [];
                    $meta = App::meta()->getMetaRecordset($rs->post_meta, 'tag');
                    $meta->sort('meta_id_lower', 'asc');
                    while ($meta->fetch()) {
                        $tags[] = '#' . self::convertTag($meta->meta_id, $mode);
                    }
                    $elements[] = implode(' ', $tags);
                }
                // URL
                $elements[] = $rs->getURL();

                $payload = [
                    'status'     => implode(' ', $elements),
                    'visibility' => 'public',       // public, unlisted, private, direct
                ];
                HttpClient::quickPost($uri, $payload);
            }
        } catch (Exception) {
        }

        return '';
    }

    /**
     * Convert a tag depending on mode
     *
     * @param      string  $tag    The tag
     * @param      int     $mode   The mode
     *
     * @return     string
     */
    private static function convertTag(string $tag, int $mode = My::TAGS_MODE_NONE): string
    {
        if (strtoupper($tag) === $tag) {
            // Don't touch all uppercased tag
            return $tag;
        }

        return match ($mode) {
            // Remove spaces
            My::TAGS_MODE_NOSPACE => str_replace(
                ' ',
                '',
                $tag
            ),
            // Uppercase each words and remove spaces
            My::TAGS_MODE_PASCALCASE => str_replace(
                ' ',
                '',
                ucwords(strtolower($tag))
            ),
            // Uppercase each words but the first and remove spaces
            My::TAGS_MODE_CAMELCASE => lcfirst(
                str_replace(
                    ' ',
                    '',
                    ucwords(strtolower($tag))
                )
            ),
            My::TAGS_MODE_NONE => $tag,
            default            => $tag,
        };
    }
}
