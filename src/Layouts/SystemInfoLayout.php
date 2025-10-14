<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

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
            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Panel::make('data')
                ->setId('system-info')
                ->setClass('mx-4 mb-4')
                ->addColumn('name')
                ->addColumn('value'),
        ];
    }
}
