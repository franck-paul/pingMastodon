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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Radio;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends Process
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if ($_POST !== []) {
            try {
                $settings = My::settings();

                $settings->put('active', !empty($_POST['pm_active']));

                $settings->put('instance', trim(Html::escapeHTML($_POST['pm_instance'])));
                $settings->put('token', trim(Html::escapeHTML($_POST['pm_token'])));
                $settings->put('prefix', trim(Html::escapeHTML($_POST['pm_prefix'])));
                $settings->put('tags', !empty($_POST['pm_tags']));
                $settings->put('tags_mode', (int) $_POST['pm_tags_mode'], App::blogWorkspace()::NS_INT);
                $settings->put('cats', !empty($_POST['pm_cats']));
                $settings->put('cats_mode', (int) $_POST['pm_cats_mode'], App::blogWorkspace()::NS_INT);

                App::blog()->triggerBlog();

                Notices::addSuccessNotice(__('Settings have been successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $settings = My::settings();

        Page::openModule(My::name());

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('Ping Mastodon')                   => '',
            ]
        );
        echo Notices::getNotices();

        // Form

        $references_mode_options_tags = [
            My::REFS_MODE_NONE       => __('No conversion'),
            My::REFS_MODE_NOSPACE    => __('Spaces will be removed'),
            My::REFS_MODE_CAMELCASE  => __('Spaces will be removed and tag will then be convert to <samp>camelCase</samp>'),
            My::REFS_MODE_PASCALCASE => __('Spaces will be removed and tag will then be convert to <samp>PascalCase</samp>'),
        ];
        $references_mode_options_cats = [
            My::REFS_MODE_NONE       => __('No conversion'),
            My::REFS_MODE_NOSPACE    => __('Spaces will be removed'),
            My::REFS_MODE_CAMELCASE  => __('Spaces will be removed and category name will then be convert to <samp>camelCase</samp>'),
            My::REFS_MODE_PASCALCASE => __('Spaces will be removed and category name will then be convert to <samp>PascalCase</samp>'),
        ];
        $tagsmodes = [];
        $catsmodes = [];
        $tags_mode = $settings->tags_mode ?? My::REFS_MODE_CAMELCASE;
        $cats_mode = $settings->cats_mode ?? My::REFS_MODE_CAMELCASE;

        $i = 0;
        foreach ($references_mode_options_tags as $k => $v) {
            $tagsmodes[] = (new Radio(['pm_tags_mode', 'pm_tags_mode-' . $i], $tags_mode == $k))
                    ->value($k)
                    ->label((new Label($v, Label::INSIDE_TEXT_AFTER)));
            ++$i;
        }
        $i = 0;
        foreach ($references_mode_options_cats as $k => $v) {
            $catsmodes[] = (new Radio(['pm_cats_mode', 'pm_cats_mode-' . $i], $cats_mode == $k))
                    ->value($k)
                    ->label((new Label($v, Label::INSIDE_TEXT_AFTER)));
            ++$i;
        }

        echo
        (new Form('ping_mastodon_params'))
            ->action(App::backend()->getPageURL())
            ->method('post')
            ->fields([
                (new Para())->items([
                    (new Checkbox('pm_active', (bool) $settings->active))
                        ->value(1)
                        ->label((new Label(__('Activate pingMastodon plugin'), Label::INSIDE_TEXT_AFTER))),
                ]),
                (new Para())->items([
                    (new Input('pm_instance'))
                        ->size(48)
                        ->maxlength(128)
                        ->value(Html::escapeHTML((string) $settings->instance))
                        ->required(true)
                        ->label((new Label(
                            (new Text('abbr', '*'))->title(__('Required field'))->render() . __('Mastodon instance:'),
                            Label::OUTSIDE_TEXT_BEFORE
                        ))->id('a11yc_label_label')->class('required')->title(__('Required field'))),
                ]),
                (new Para())->items([
                    (new Input('pm_token'))
                        ->size(64)
                        ->maxlength(128)
                        ->value(Html::escapeHTML((string) $settings->token))
                        ->required(true)
                        ->label((new Label(
                            (new Text('abbr', '*'))->title(__('Required field'))->render() . __('Application token:'),
                            Label::OUTSIDE_TEXT_BEFORE
                        ))->id('a11yc_label_label')->class('required')->title(__('Required field'))),
                ]),
                (new Para())->items([
                    (new Input('pm_prefix'))
                        ->size(30)
                        ->maxlength(128)
                        ->value(Html::escapeHTML((string) $settings->prefix))
                        ->label((new Label(__('Status prefix:'), Label::OUTSIDE_TEXT_BEFORE))),
                ]),
                (new Fieldset())
                ->legend(new Legend(__('Tags')))
                ->fields([
                    (new Para())->items([
                        (new Checkbox('pm_tags', (bool) $settings->tags))
                            ->value(1)
                            ->label((new Label(__('Include tags'), Label::INSIDE_TEXT_AFTER))),
                    ]),
                    (new Para())->class('pretty-title')->items([
                        (new Text(null, __('Tags conversion mode:'))),
                    ]),
                    ...$tagsmodes,
                ]),
                (new Fieldset())
                ->legend(new Legend(__('Categories')))
                ->fields([
                    (new Para())->items([
                        (new Checkbox('pm_cats', (bool) $settings->cats))
                            ->value(1)
                            ->label((new Label(__('Include categories'), Label::INSIDE_TEXT_AFTER))),
                    ]),
                    (new Note())
                        ->class('form-note')
                        ->text(__('Will include category\'s parents')),
                    (new Para())->class('pretty-title')->items([
                        (new Text(null, __('Categories conversion mode:'))),
                    ]),
                    ...$catsmodes,
                ]),
                // Submit
                (new Para())->items([
                    (new Submit(['frmsubmit']))
                        ->value(__('Save')),
                    ... My::hiddenFields(),
                ]),
            ])
        ->render();

        Page::closeModule();
    }
}
