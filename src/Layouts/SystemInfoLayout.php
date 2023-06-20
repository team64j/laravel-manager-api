<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Title;

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
                ->setClass('py-4'),
        ];
    }

    /**
     * @return array
     */
    public function title(): array
    {
        return [
            'title' => Lang::get('global.view_sysinfo'),
            'icon' => 'fa fa-info',
        ];
    }
}
