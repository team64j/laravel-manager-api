<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Title;

class SystemInfoLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Title::make()
                ->setTitle(Lang::get('global.view_sysinfo'))
                ->setIcon('fa fa-info'),

            Panel::make()
                ->setId('system-info')
                ->setModel('data')
                ->addColumn('name')
                ->addColumn('value'),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-info';
    }
}
