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
    protected const CATCHPHRASE_METATYPE = 'ping_mastodon_catchphrase';

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

        $instance   = $settings->instance;
        $token      = $settings->token;
        $prefix     = $settings->prefix;
        $visibility = $settings->visibility;
        $addtags    = $settings->tags;
        $tagsmode   = $settings->tags_mode;
        $addcats    = $settings->cats;
        $catsmode   = $settings->cats_mode;

        if (empty($instance) || empty($token) || $ids === []) {
            return '';
        }

        // Prepare instance URI
        if (!parse_url((string) $instance, PHP_URL_HOST)) {
            $instance = 'https://' . $instance;
        }

        $uri = rtrim((string) $instance, '/') . '/api/v1/statuses?access_token=' . $token;

        try {
            // Get posts information
            $rs = $blog->getPosts(['post_id' => $ids]);
            $rs->extend(Post::class);
            while ($rs->fetch()) {
                $elements    = [];
                $catchphrase = $settings->catchphrase ? self::getCatchPhrase((int) $rs->post_id) : '';
                if ($catchphrase !== '') {
                    $catchphrase .= "\n";
                }
                // [Prefix] [Catchphrase] Title
                $elements[] = (empty($prefix) ? '' : $prefix . ' ') . $catchphrase . $rs->post_title;
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
                    'visibility' => $visibility ?? 'public',
                ];

                // Check if an image is avalaible, and if so send it and get its media_id

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
                ucwords(mb_convert_case(strtolower((string) $reference), MB_CASE_TITLE, 'UTF-8'))
            ),
            // Uppercase each words but the first and remove spaces
            My::REFS_MODE_CAMELCASE => lcfirst(
                str_replace(
                    ' ',
                    '',
                    ucwords(mb_convert_case(strtolower((string) $reference), MB_CASE_TITLE, 'UTF-8'))
                )
            ),
            My::REFS_MODE_NONE => $reference,
            default            => $reference,
        };
    }

    public static function getCatchPhrase(int $post_id): string
    {
        if ($post_id === 0) {
            return '';
        }

        $meta      = App::meta();
        $post_meta = $meta->getMetadata([
            'meta_type' => self::CATCHPHRASE_METATYPE,
            'post_id'   => $post_id,
        ]);
        while ($post_meta->fetch()) {
            // Return 1st found meta value
            return $post_meta->meta_id;
        }

        return '';
    }

    public static function setCatchPhrase(int $post_id, string $catchphrase): void
    {
        if ($post_id === 0) {
            return;
        }

        $meta = App::meta();
        $meta->delPostMeta($post_id, self::CATCHPHRASE_METATYPE);
        if ($catchphrase !== '') {
            $meta->setPostMeta($post_id, self::CATCHPHRASE_METATYPE, $catchphrase);
        }
    }
}
