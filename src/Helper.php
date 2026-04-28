<?php

/**
 * @brief pingMastodon, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
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
    public static function ping(BlogInterface $blog, array $ids, bool $ignore_category = false): string
    {
        $settings = My::settings();
        if (!$settings->active) {
            return '';
        }

        $instance    = is_string($instance = $settings->instance) ? $instance : '';
        $token       = is_string($token = $settings->token) ? $token : '';
        $prefix      = is_string($prefix = $settings->prefix) ? $prefix : '';
        $visibility  = is_string($visibility = $settings->visibility) ? $visibility : 'public';
        $addtags     = is_bool($addtags = $settings->tags) && $addtags;
        $tagsmode    = is_numeric($tagsmode = $settings->tags_mode) ? (int) $tagsmode : My::REFS_MODE_CAMELCASE;
        $addcats     = is_bool($addcats = $settings->cats) && $addcats;
        $catsmode    = is_numeric($catsmode = $settings->cats_mode) ? (int) $catsmode : My::REFS_MODE_CAMELCASE;
        $only_cat    = is_bool($only_cat = $settings->only_cat) && $only_cat;
        $only_cat_id = is_numeric($only_cat_id = $settings->only_cat_id) ? (int) $only_cat_id : 0;

        if ($instance === '' || $token === '' || $ids === []) {
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
                $post_id = is_numeric($post_id = $rs->post_id) ? (int) $post_id : 0;
                if ($post_id === 0) {
                    // We should not have a post_id = 0, but who knows?
                    continue;
                }

                $cat_id = is_numeric($cat_id = $rs->cat_id) ? (int) $cat_id : 0;
                if ($ignore_category === false && $only_cat && $cat_id !== $only_cat_id) {
                    // We do not ignore category and
                    // the article's category isn't the only one that needs to be taken into account
                    continue;
                }

                $post_title = is_string($post_title = $rs->post_title) ? $post_title : '';

                $elements    = [];
                $catchphrase = $settings->catchphrase ? self::getCatchPhrase($post_id) : '';
                if ($catchphrase !== '') {
                    $catchphrase .= "\n";
                }
                // [Prefix] [Catchphrase] Title
                $elements[] = ($prefix === '' ? '' : $prefix . ' ') . $catchphrase . $post_title;
                // References (categories, tags)
                $references = [];
                // Categories
                if ($addcats && $cat_id !== 0) {
                    $rscats = App::blog()->getCategoryParents($cat_id);
                    while ($rscats->fetch()) {
                        $cat_title = is_string($cat_title = $rscats->cat_title) ? $cat_title : '';
                        if ($cat_title !== '') {
                            $references[] = '#' . self::convertRef($cat_title, $catsmode);
                        }
                    }
                    $cat_title = is_string($cat_title = $rs->cat_title) ? $cat_title : '';
                    if ($cat_title !== '') {
                        $references[] = '#' . self::convertRef($cat_title, $catsmode);
                    }
                }
                // Tags
                if ($addtags) {
                    $post_meta = is_string($post_meta = $rs->post_meta) ? $post_meta : '';
                    if ($post_meta !== '') {
                        $meta = App::meta()->getMetaRecordset($post_meta, 'tag');
                        $meta->sort('meta_id_lower', 'asc');
                        while ($meta->fetch()) {
                            $meta_id = is_string($meta_id = $meta->meta_id) ? $meta_id : '';
                            if ($meta_id !== '') {
                                $references[] = '#' . self::convertRef($meta_id, $tagsmode);
                            }
                        }
                    }
                }
                $references = array_unique($references);
                if ($references !== []) {
                    $elements[] = implode(' ', $references);
                }
                // URL
                $post_url = is_string($post_url = $rs->getURL()) ? $post_url : '';
                if ($post_url === '') {
                    continue;
                }
                $elements[] = $post_url;

                $payload = [
                    'status'     => implode("\n", $elements),
                    'visibility' => $visibility,
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
            // Return 1st found non empty meta value
            $meta_id = is_string($meta_id = $post_meta->meta_id) ? $meta_id : '';
            if ($meta_id !== '') {
                return $meta_id;
            }
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
