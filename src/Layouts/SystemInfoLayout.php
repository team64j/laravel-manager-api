<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class SystemInfoLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-info';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.view_sysinfo');
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Tabs::make()
                ->setId('resource')
                ->addTab(
                    'general',
                    __('global.settings_general'),
                    slot: [
                        Panel::make('data')
                            ->setId('system-info')
                            ->addColumn('name')
                            ->addColumn('value'),
                    ]
                ),
        ];
    }
}
