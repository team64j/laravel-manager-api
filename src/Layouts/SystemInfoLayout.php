<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
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
        return Lang::get('global.view_sysinfo');
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

            Tabs::make()
                ->addTab(
                    'default',
                    slot: Panel::make()
                        ->setId('system-info')
                        ->setModel('data')
                        ->addColumn('name')
                        ->addColumn('value'),
                ),
        ];
    }
}
