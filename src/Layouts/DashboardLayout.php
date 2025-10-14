<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

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
            $data[] = Template::make()
                ->setClass('block app-alert__warning p-4 mt-4 mx-4 rounded')
                ->setSlot(
                    __('global.siteunavailable_message_default') .
                    ' ' . __('global.update_settings_from_language') .
                    '<a href="/configuration" class="btn-sm btn-green ml-2">' . __('global.online') . '</a>'
                );
        }

        if (is_dir(base_path('install'))) {
            $data[] = Template::make()
            ->setClass('block app-alert__warning p-4 mt-4 mx-4 rounded')
            ->setSlot('<strong>' . __('global.configcheck_warning') . '</strong>' .
                '<br>' . __('global.configcheck_installer') .
                '<br><br><i>' . __('global.configcheck_what') . '</i>' .
                '<br>' . __('global.configcheck_installer_msg'));
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
            ->setClass('flex flex-wrap pt-6 px-4')
            ->setSlot([
                Template::make()
                    ->setClass('grow w-full xl:max-w-[50%] xl:pr-2 pb-4')
                    ->setSlot(
                        Section::make()
                            ->setIcon('fa fa-home')
                            ->setLabel(__('global.welcome_title'))
                            ->setClass('h-full hover:shadow-lg bg-white dark:bg-gray-700 transition')
                            ->setSlot(
                                Panel::make()
                                    ->setClass('!mt-0 !-mx-4 !-mb-4')
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
                            )
                    ),

                Template::make()
                    ->setClass('grow w-full xl:max-w-[50%] xl:pl-2 pb-4')
                    ->setSlot(
                        Section::make()
                            ->setIcon('fa fa-user')
                            ->setLabel(__('global.onlineusers_title'))
                            ->setClass('h-full hover:shadow-lg bg-white dark:bg-gray-700 transition')
                            ->setSlot(
                                [
                                    '<div class="mb-4">' . __('global.onlineusers_message') . '<b>' .
                                    date('H:i:s') . '</b>)</div>',

                                    Panel::make()
                                        ->setId('widgetUsers')
                                        ->setClass('!mt-0 !-mx-4 !-mb-4')
                                        ->setRoute('/users/:id')
                                        ->setUrl('/users/active'),
                                ]
                            )
                    ),
            ])
            ->when(
                auth()->user()->can(['view_document']),
                fn(Template $template) => $template->putSlot(
                    Template::make()
                        ->setClass('grow w-full pb-4')
                        ->setSlot(
                            Section::make()
                                ->setIcon('fa fa-pencil')
                                ->setLabel(__('global.activity_title'))
                                ->setClass('hover:shadow-lg bg-white dark:bg-gray-700 overflow-hidden transition')
                                ->setSlot(
                                    Panel::make()
                                        ->setId('widgetResources')
                                        ->setClass('!mt-0 !-mx-4 !-mb-4')
                                        ->setRoute('/resource/:id')
                                        ->setUrl(
                                            '/resource?order=createdon&dir=desc&limit=10&columns=id,pagetitle,longtitle,createdon'
                                        )
                                )
                        )
                )
            )
            ->when(
                config('global.rss_url_news'),
                fn(Template $template) => $template->putSlot(
                    Template::make()
                        ->setClass('grow w-full xl:max-w-[50%] xl:pr-2 pb-4')
                        ->setSlot(
                            Section::make()
                                ->setIcon('fa fa-rss')
                                ->setLabel(__('global.modx_news'))
                                ->setClass('overflow-hidden bg-white dark:bg-gray-700 hover:shadow-lg transition')
                                ->setSlot(
                                    Panel::make()
                                        ->setId('widgetNews')
                                        ->setClass('h-40 !mt-0 !-mx-4 !-mb-4')
                                        ->setUrl('/dashboard/news')
                                )
                        )
                )
            )
            ->when(
                config('global.rss_url_security'),
                fn(Template $template) => $template->putSlot(
                    Template::make()
                        ->setClass('grow w-full xl:max-w-[50%] xl:pl-2 pb-4')
                        ->setSlot(
                            Section::make()
                                ->setIcon('fa fa-exclamation-triangle')
                                ->setLabel(__('global.modx_security_notices'))
                                ->setClass('overflow-hidden bg-white dark:bg-gray-700 hover:shadow-lg transition')
                                ->setSlot(
                                    Panel::make()
                                        ->setId('widgetNewsSecurity')
                                        ->setClass('h-40 !mt-0 !-mx-4 !-mb-4 !rounded-none')
                                        ->setUrl('/dashboard/news-security')
                                )
                        )
                )
            )
            ->toArray();
    }
}
