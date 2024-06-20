<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Section;
use Team64j\LaravelManagerApi\Components\Template;

class DashboardLayout extends Layout
{
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
            'flex flex-wrap items-baseline pt-6 px-4 dark:bg-gray-800'
        )
            ->putSlot(
                Template::make(
                    'grow w-full xl:max-w-[50%] xl:pr-2',
                    Section::make(
                        'fa fa-home',
                        Lang::get('global.welcome_title'),
                        'lg:min-h-[15rem] content-baseline bg-white dark:bg-gray-750 hover:shadow-lg transition',
                        '<div class="data"><table>' .
                        '<tr><td class="w-52">' . Lang::get('global.yourinfo_username') .
                        '</td><td><strong>' .
                        Auth::user()->username . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_role') . '</td><td><strong>' .
                        Auth::user()->attributes->userRole->name . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_previous_login') . '</td><td><strong>' .
                        Auth::user()->attributes->lastlogin . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_total_logins') . '</td><td><strong>' .
                        Auth::user()->attributes->logincount . '</strong></td></tr>' .
                        '</table></div>'
                    )
                )
            )
            ->putSlot(
                Template::make(
                    'grow w-full xl:max-w-[50%] xl:pl-2',
                    Section::make(
                        'fa fa-user',
                        Lang::get('global.onlineusers_title'),
                        'lg:min-h-[15rem] content-baseline bg-white dark:bg-gray-750 hover:shadow-lg transition',
                        [
                            '<div class="mb-4">' . Lang::get('global.onlineusers_message') . '<b>' .
                            date('H:i:s') . '</b>)</div>',
                            Panel::make()
                                ->setId('widgetUsers')
                                ->setClass('!mt-0 !-mx-4 !-mb-4 !rounded-none')
                                ->setHistory(false)
                                ->setRoute('/users/:id')
                                ->setUrl('/users/active'),
                        ]
                    )
                )
            )
            ->when(
                Gate::check(['view_document']),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full',
                        Section::make(
                            'fa fa-pencil',
                            Lang::get('global.activity_title'),
                            'hover:shadow-lg bg-white dark:bg-gray-750 overflow-hidden transition',
                            Panel::make()
                                ->setId('widgetResources')
                                ->setClass('!mt-0 !-mx-4 !-mb-4 !rounded-none')
                                ->setHistory(false)
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
                        'grow w-full xl:max-w-[50%] xl:pr-2 pb-2',
                        Section::make(
                            'fa fa-rss',
                            Lang::get('global.modx_news'),
                            'overflow-hidden bg-white dark:bg-gray-750 hover:shadow-lg transition',
                            Panel::make()
                                ->setId('widgetNews')
                                ->setClass('h-40 !mt-0 !-mx-4 !-mb-4 !rounded-none')
                                ->setUrl('/dashboard/news')
                        )
                    )
                )
            )
            ->when(
                Config::get('global.rss_url_security'),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full xl:max-w-[50%] xl:pl-2 pb-2',
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
