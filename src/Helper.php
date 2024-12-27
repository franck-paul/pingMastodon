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
        $tagsmode = $settings->tags_mode;
        $addcats  = $settings->cats;
        $catsmode = $settings->cats_mode;

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
                // [Prefix] Title
                $elements[] = (empty($prefix) ? '' : $prefix . ' ') . $rs->post_title;
                // References (categories, tags)
                $references = [];
                // Categories
                if ($addcats && $rs->cat_id) {
                    $rscats = App::blog()->getCategoryParents((int) $rs->cat_id);
                    while ($rscats->fetch()) {
                        $references[] = '#' . self::convertRef($rscats->cat_title, $catsmode);
                    }
                    $references[] = '#' . self::convertRef($rs->cat_title, $catsmode);
                }
                // Tags
                if ($addtags) {
                    $meta = App::meta()->getMetaRecordset($rs->post_meta, 'tag');
                    $meta->sort('meta_id_lower', 'asc');
                    while ($meta->fetch()) {
                        $references[] = '#' . self::convertRef($meta->meta_id, $tagsmode);
                    }
                }
                $references = array_unique($references);
                if ($references !== []) {
                    $elements[] = implode(' ', $references);
                }
                // URL
                $elements[] = $rs->getURL();

                $payload = [
                    'status'     => implode("\n", $elements),
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
     * @param      string  $reference   The tag
     * @param      int     $mode        The mode
     */
    private static function convertRef(string $reference, int $mode = My::REFS_MODE_NONE): string
    {
        // Mastodon Hashtags can contain alphanumeric characters and underscores,
        // Replace other (but spaces) with underscores.
        // \pL stands for any character in any language
        $reference = (string) preg_replace('/[^\pL\s\d]/mu', '_', $reference);

        if (strtoupper($reference) === $reference) {
            // Don't touch all uppercased tag
            return $reference;
        }

        return match ($mode) {
            // Remove spaces
            My::REFS_MODE_NOSPACE => str_replace(
                ' ',
                '',
                $reference
            ),
            // Uppercase each words and remove spaces
            My::REFS_MODE_PASCALCASE => str_replace(
                ' ',
                '',
                ucwords(strtolower($reference))
            ),
            // Uppercase each words but the first and remove spaces
            My::REFS_MODE_CAMELCASE => lcfirst(
                str_replace(
                    ' ',
                    '',
                    ucwords(strtolower($reference))
                )
            ),
            My::REFS_MODE_NONE => $reference,
            default            => $reference,
        };
    }
}
