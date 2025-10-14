<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Alert;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Section;
use Team64j\LaravelManagerComponents\Template;

class DashboardLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-home';
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            $this->getMessages(),
            $this->getWidgets(),
        ];
    }

    /**
     * @return array
     */
    protected function getMessages(): array
    {
        $data = [];

        if (!config('global.site_status')) {
            $data[] = Alert::make()
                ->setType('warning')
                ->setSlot(
                    __('global.siteunavailable_message_default') .
                    ' ' . __('global.update_settings_from_language') .
                    '<a href="/configuration" class="btn-sm btn-green ml-2">' . __('global.online') . '</a>'
                );
        }

        if (is_dir(base_path('install'))) {
            $data[] = Alert::make()
                ->setType('warning')
                ->setSlot(
                    '<strong>' . __('global.configcheck_warning') . '</strong>' .
                    '<br>' . __('global.configcheck_installer') .
                    '<br><br><i>' . __('global.configcheck_what') . '</i>' .
                    '<br>' . __('global.configcheck_installer_msg')
                );
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getWidgets(): array
    {
        $user = auth()->user();

        return Template::make()
            ->setSlot([
                Section::make()
                    ->setIcon('fa fa-home')
                    ->setLabel(__('global.welcome_title'))
                    ->setSlot(
                        Panel::make()
                            ->addColumn('name', style: ['width' => '1%', 'white-space' => 'nowrap'])
                            ->addColumn('value', style: ['font-weight' => 'bold'])
                            ->setData([
                                [
                                    'name'  => __('global.yourinfo_username'),
                                    'value' => $user->username,
                                ],
                                [
                                    'name'  => __('global.yourinfo_role'),
                                    'value' => $user->attributes->userRole->name,
                                ],
                                [
                                    'name'  => __('global.yourinfo_previous_login'),
                                    'value' => $user->attributes->lastlogin,
                                ],
                                [
                                    'name'  => __('global.yourinfo_total_logins'),
                                    'value' => $user->attributes->logincount,
                                ],
                            ])
                    ),

                Section::make()
                    ->setIcon('fa fa-user')
                    ->setLabel(__('global.onlineusers_title'))
                    ->setSlot(
                        [
                            '<p>' . __('global.onlineusers_message') . '<b>' . date('H:i:s') . '</b>)</p>',

                            Panel::make()
                                ->setId('widgetUsers')
                                ->setRoute('/users/:id')
                                ->setUrl('/users/active'),
                        ]
                    ),
            ])
            ->when(
                auth()->user()->can(['view_document']),
                fn(Template $template) => $template->putSlot(
                    Section::make()
                        ->setIcon('fa fa-pencil')
                        ->setLabel(__('global.activity_title'))
                        ->setSlot(
                            Panel::make()
                                ->setId('widgetResources')
                                ->setRoute('/resource/:id')
                                ->setUrl(
                                    '/resource?order=createdon&dir=desc&limit=10&columns=id,pagetitle,longtitle,createdon'
                                )
                        )
                )
            )
            ->when(
                config('global.rss_url_news'),
                fn(Template $template) => $template->putSlot(
                    Section::make()
                        ->setIcon('fa fa-rss')
                        ->setLabel(__('global.modx_news'))
                        ->setSlot(
                            Panel::make()
                                ->setId('widgetNews')
                                ->setUrl('/dashboard/news')
                        )
                )
            )
            ->when(
                config('global.rss_url_security'),
                fn(Template $template) => $template->putSlot(
                    Section::make()
                        ->setIcon('fa fa-exclamation-triangle')
                        ->setLabel(__('global.modx_security_notices'))
                        ->setSlot(
                            Panel::make()
                                ->setId('widgetNewsSecurity')
                                ->setUrl('/dashboard/news-security')
                        )
                )
            )
            ->toArray();
    }
}
