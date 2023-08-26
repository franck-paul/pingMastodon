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

use dcCore;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
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

        if (!empty($_POST)) {
            try {
                $settings = My::settings();

                $settings->put('active', !empty($_POST['pm_active']));

                $settings->put('instance', trim(Html::escapeHTML($_POST['pm_instance'])));
                $settings->put('token', trim(Html::escapeHTML($_POST['pm_token'])));
                $settings->put('prefix', trim(Html::escapeHTML($_POST['pm_prefix'])));

                dcCore::app()->blog->triggerBlog();

                Notices::addSuccessNotice(__('Settings have been successfully updated.'));
                dcCore::app()->admin->url->redirect('admin.plugin.' . My::id());
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
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
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Ping Mastodon')                         => '',
            ]
        );
        echo Notices::getNotices();

        // Form
        echo
        (new Form('a11y_params'))
            ->action(dcCore::app()->admin->getPageURL())
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
                        ->required(true)
                        ->label((new Label(__('Status prefix:'), Label::OUTSIDE_TEXT_BEFORE))),
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
