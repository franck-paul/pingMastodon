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
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Radio;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

class Manage
{
    use TraitProcess;

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
            $_Bool = fn (string $name): bool => !empty($_POST[$name]);
            $_Int  = fn (string $name, int $default = 0): int => isset($_POST[$name]) && is_numeric($val = $_POST[$name]) ? (int) $val : $default;
            $_Str  = fn (string $name, string $default = ''): string => isset($_POST[$name]) && is_string($val = $_POST[$name]) ? trim($val) : $default;

            try {
                $settings = My::settings();

                $settings->put('active', $_Bool('pm_active'), App::blogWorkspace()::NS_BOOL);

                $settings->put('instance', $_Str('pm_instance'), App::blogWorkspace()::NS_STRING);
                $settings->put('token', $_Str('pm_token'), App::blogWorkspace()::NS_STRING);
                $settings->put('prefix', $_Str('pm_prefix'), App::blogWorkspace()::NS_STRING);
                $settings->put('visibility', $_Str('pm_visibility'), App::blogWorkspace()::NS_STRING);
                $settings->put('catchphrase', $_Bool('pm_catchphrase'), App::blogWorkspace()::NS_BOOL);
                $settings->put('tags', $_Bool('pm_tags'), App::blogWorkspace()::NS_BOOL);
                $settings->put('tags_mode', $_Int('pm_tags_mode'), App::blogWorkspace()::NS_INT);
                $settings->put('cats', $_Bool('pm_cats'), App::blogWorkspace()::NS_BOOL);
                $settings->put('cats_mode', $_Int('pm_cats_mode'), App::blogWorkspace()::NS_INT);
                $settings->put('auto_ping', $_Bool('pm_auto_ping'), App::blogWorkspace()::NS_BOOL);
                $settings->put('only_cat', $_Bool('pm_only_cat'), App::blogWorkspace()::NS_BOOL);
                $settings->put('only_cat_id', $_Int('pm_only_cat_id'), App::blogWorkspace()::NS_INT);

                App::blog()->triggerBlog();

                App::backend()->notices()->addSuccessNotice(__('Settings have been successfully updated.'));
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

        App::backend()->page()->openModule(My::name());

        echo App::backend()->page()->breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('Ping Mastodon')                   => '',
            ]
        );
        echo App::backend()->notices()->getNotices();

        // Form

        $instance    = is_string($instance = $settings->instance) ? $instance : '';
        $token       = is_string($token = $settings->token) ? $token : '';
        $prefix      = is_string($prefix = $settings->prefix) ? $prefix : '';
        $visibility  = is_string($visibility = $settings->visibility) ? $visibility : 'public';
        $auto_ping   = is_bool($auto_ping = $settings->auto_ping) ? $auto_ping : true;
        $only_cat_id = is_numeric($only_cat_id = $settings->only_cat_id) ? (int) $only_cat_id : 0;

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

        $visibilities = [
            __('Public')   => 'public',
            __('Unlisted') => 'unlisted',
            __('Private')  => 'private',
            __('Direct')   => 'direct',
        ];

        $categories_combo = App::backend()->combos()->getCategoriesCombo(
            App::blog()->getCategories()
        );

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
                        ->value(Html::escapeHTML($instance))
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
                        ->value(Html::escapeHTML($token))
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
                        ->value(Html::escapeHTML($prefix))
                        ->label((new Label(__('Status prefix:'), Label::OUTSIDE_TEXT_BEFORE))),
                ]),
                (new Para())->items([
                    (new Select('pm_visibility'))
                        ->items($visibilities)
                        ->default(Html::escapeHTML($visibility))
                        ->label(new Label(__('Status visibility:'), Label::OUTSIDE_LABEL_BEFORE)),
                ]),
                (new Fieldset())
                    ->legend(new Legend(__('Automatic ping')))
                    ->fields([
                        (new Checkbox('pm_auto_ping', $auto_ping))
                            ->value(1)
                            ->label((new Label(__('Automatically ping when an entry is first published'), Label::INSIDE_TEXT_AFTER))),
                        (new Para())->items([
                            (new Checkbox('pm_only_cat', (bool) $settings->only_cat))
                                ->value(1)
                                ->label((new Label(__('Restrict automatic ping to one category only'), Label::INSIDE_TEXT_AFTER))),
                        ]),
                        (new Para())
                            ->items([
                                (new Select('pm_only_cat_id'))
                                    ->items($categories_combo)
                                    ->default($only_cat_id)
                                    ->label(new Label(__('Category:'), Label::IL_TF)),
                            ]),

                    ]),
                (new Fieldset())
                    ->legend(new Legend(__('Catchphrase')))
                    ->fields([
                        (new Para())->items([
                            (new Checkbox('pm_catchphrase', (bool) $settings->catchphrase))
                                ->value(1)
                                ->label((new Label(__('Use entry catchphrase if available'), Label::INSIDE_TEXT_AFTER))),
                        ]),
                        (new Note())
                            ->class('form-note')
                            ->text(__('The catchphrase is defined for each entry, see the options when creating/editing it.')),
                    ]),
                (new Fieldset())
                    ->legend(new Legend(__('Tags')))
                    ->fields([
                        (new Para())->items([
                            (new Checkbox('pm_tags', (bool) $settings->tags))
                                ->value(1)
                                ->label((new Label(__('List tags as hashtags'), Label::INSIDE_TEXT_AFTER))),
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
                                ->label((new Label(__('List the names of the categories as hashtags'), Label::INSIDE_TEXT_AFTER))),
                        ]),
                        (new Note())
                            ->class('form-note')
                            ->text(__('It will also add the names of the parent categories')),
                        (new Para())->class('pretty-title')->items([
                            (new Text(null, __('Category names conversion mode:'))),
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

        App::backend()->page()->closeModule();
    }
}
