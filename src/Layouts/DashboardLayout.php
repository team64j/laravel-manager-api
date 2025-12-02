<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Alert;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Section;

class DashboardLayout extends Layout
{
    public function title(): string
    {
        return '';
    }

    public function icon(): string
    {
        return 'fa fa-home';
    }

    public function default(): array
    {
        return [
            GlobalTab::make()
                ->isFixed()
                ->setIcon($this->icon()),

            $this->getMessages(),
            $this->getWidgets(),
        ];
    }

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

    protected function getWidgets()
    {
        $user = auth()->user();

        return Grid::make()
            ->setGap('1.25rem')
            ->setAttribute('style', 'padding: 1.25rem')
            ->addArea([
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
            ], ['sm' => '1', 'xl' => '1 / 1'])
            ->addArea([
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
            ], ['sm' => '2', 'xl' => '1 / 2'])
            ->when(
                auth()->user()->can(['view_document']),
                fn(Grid $grid) => $grid->addArea([
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
                        ),
                ], ['sm' => '3', 'xl' => '2 / 1 / 2 / 3'])
            )
            ->when(
                config('global.rss_url_news'),
                fn(Grid $grid) => $grid->addArea([
                    Section::make()
                        ->setIcon('fa fa-rss')
                        ->setLabel(__('global.modx_news'))
                        ->setSlot(
                            Panel::make()
                                ->setId('widgetNews')
                                ->setUrl('/dashboard/news')
                        ),
                ], ['sm' => '4', 'xl' => '1 / 1 / 1 / 2'])
            )
            ->when(
                config('global.rss_url_security'),
                fn(Grid $grid) => $grid->addArea([
                    Section::make()
                        ->setIcon('fa fa-exclamation-triangle')
                        ->setLabel(__('global.modx_security_notices'))
                        ->setSlot(
                            Panel::make()
                                ->setId('widgetNewsSecurity')
                                ->setUrl('/dashboard/news-security')
                        ),
                ], ['sm' => '5', 'xl' => '1 / 1 / 1 / 2'])
            );
    }
}
