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

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Textarea;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    /**
     * Add ping Mastodon fieldset in entry sidebar.
     *
     * @param   ArrayObject<string, mixed>     $main       The main part of the entry form
     * @param   ArrayObject<string, mixed>     $sidebar    The sidebar part of the entry form
     * @param   MetaRecord                     $post       The post
     */
    public static function adminPostFormItems(ArrayObject $main, ArrayObject $sidebar, ?MetaRecord $post): string
    {
        $settings = My::settings();
        if (!$settings->active) {
            return '';
        }

        if (!empty($_POST['ping-mastodon-catchphrase'])) {
            $catchphrase = $_POST['ping-mastodon-catchphrase'];
        } else {
            $catchphrase = $post instanceof MetaRecord ? Helper::getCatchPhrase((int) $post->post_id) : '';
        }

        $div = (new Div())
            ->items([
                (new Text('h5', __('Ping Mastodon')))->class('ping-mastodon'),
                (new Para())
                    ->class('ping-mastodon')
                    ->items([
                        (new Textarea('ping-mastodon-catchphrase', $catchphrase))
                            ->rows(4)
                            ->cols(25)
                            ->maxlength(255)
                            ->label(new Label(__('Catchphrase:'), Label::OL_TF)),
                    ]),
            ])
        ->render();

        $sidebar['options-box']['items']['ping_mastodon'] = $div;

        return '';
    }

    /**
     * @param   Cursor  $cur        The current
     * @param   mixed   $post_id    The post identifier
     */
    public static function setCatchPhrase(Cursor $cur, $post_id): void
    {
        if (!My::settings()->active) {
            return;
        }

        $catchphrase = $_POST['ping-mastodon-catchphrase'] ?? '';
        Helper::setCatchPhrase((int) $post_id, Html::escapeHTML($catchphrase));
    }

    public static function adminPostsActions(ActionsPosts $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Mastodon') => [__('Ping Mastodon') => 'pingMastodon']],
                self::adminPingMastodon(...)
            );
        }

        return '';
    }

    public static function adminPagesActions(PagesBackendActions $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Mastodon') => [__('Ping Mastodon') => 'pingMastodon']],
                self::adminPingMastodon(...)
            );
        }

        return '';
    }

    /**
     * @param      ActionsPosts|PagesBackendActions     $ap     Actions
     */
    public static function adminPingMastodon(ActionsPosts|PagesBackendActions $ap): void
    {
        $rs = $ap->getRS();
        if ($rs->rows()) {
            $ids = [];
            while ($rs->fetch()) {
                if ((int) $rs->post_status === App::status()->post()::PUBLISHED) {
                    // Ping only published entry
                    $ids[] = $rs->post_id;
                }
            }

            if ($ids !== []) {
                Helper::ping(App::blog(), $ids);
                App::backend()->notices()->addSuccessNotice(__('All entries have been ping to Mastodon.'));
            }

            $ap->redirect(true);
        } else {
            $ap->redirect();
        }
    }
}
