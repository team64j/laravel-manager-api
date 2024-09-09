<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
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

        if (!Config::get('global.site_status')) {
            $data[] = Template::make(
                'block app-alert__warning p-4 mt-4 mx-4 rounded',
                Lang::get('global.siteunavailable_message_default') .
                ' ' . Lang::get('global.update_settings_from_language') .
                '<a href="/configuration" class="btn-sm btn-green ml-2">' . Lang::get('global.online') . '</a>'
            );
        }

        if (is_dir(base_path('install'))) {
            $data[] = Template::make(
                'block app-alert__warning p-4 mt-4 mx-4 rounded',
                '<strong>' . Lang::get('global.configcheck_warning') . '</strong>' .
                '<br>' . Lang::get('global.configcheck_installer') .
                '<br><br><i>' . Lang::get('global.configcheck_what') . '</i>' .
                '<br>' . Lang::get('global.configcheck_installer_msg')
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getWidgets(): array
    {
        return Template::make(
            'flex flex-wrap pt-6 px-4',
            [
                Template::make(
                    'grow w-full xl:max-w-[50%] xl:pr-2 pb-4',
                    Section::make(
                        'fa fa-home',
                        Lang::get('global.welcome_title'),
                        'h-full',
                        Panel::make()
                            ->setClass('!mt-0 !-mx-4 !-mb-4')
                            ->addColumn('name', style: ['width' => '1%', 'white-space' => 'nowrap'])
                            ->addColumn('value', style: ['font-weight' => 'bold'])
                            ->setData([
                                [
                                    'name' => Lang::get('global.yourinfo_username'),
                                    'value' => Auth::user()->username,
                                ],
                                [
                                    'name' => Lang::get('global.yourinfo_role'),
                                    'value' => Auth::user()->attributes->userRole->name,
                                ],
                                [
                                    'name' => Lang::get('global.yourinfo_previous_login'),
                                    'value' => Auth::user()->attributes->lastlogin,
                                ],
                                [
                                    'name' => Lang::get('global.yourinfo_total_logins'),
                                    'value' => Auth::user()->attributes->logincount,
                                ],
                            ])
                    )
                ),

                Template::make(
                    'grow w-full xl:max-w-[50%] xl:pl-2 pb-4',
                    Section::make(
                        'fa fa-user',
                        Lang::get('global.onlineusers_title'),
                        'h-full',
                        [
                            '<div class="mb-4">' . Lang::get('global.onlineusers_message') . '<b>' .
                            date('H:i:s') . '</b>)</div>',

                            Panel::make()
                                ->setId('widgetUsers')
                                ->setClass('!mt-0 !-mx-4 !-mb-4')
                                ->setRoute('/users/:id')
                                ->setUrl('/users/active'),
                        ]
                    )
                ),
            ]
        )
            ->when(
                Gate::check(['view_document']),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full pb-4',
                        Section::make(
                            'fa fa-pencil',
                            Lang::get('global.activity_title'),
                            'hover:shadow-lg bg-white dark:bg-gray-750 overflow-hidden transition',
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
                Config::get('global.rss_url_news'),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full xl:max-w-[50%] xl:pr-2 pb-4',
                        Section::make(
                            'fa fa-rss',
                            Lang::get('global.modx_news'),
                            'overflow-hidden bg-white dark:bg-gray-750 hover:shadow-lg transition',
                            Panel::make()
                                ->setId('widgetNews')
                                ->setClass('h-40 !mt-0 !-mx-4 !-mb-4')
                                ->setUrl('/dashboard/news')
                        )
                    )
                )
            )
            ->when(
                Config::get('global.rss_url_security'),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full xl:max-w-[50%] xl:pl-2 pb-4',
                        Section::make(
                            'fa fa-exclamation-triangle',
                            Lang::get('global.modx_security_notices'),
                            'overflow-hidden bg-white dark:bg-gray-750 hover:shadow-lg transition',
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
