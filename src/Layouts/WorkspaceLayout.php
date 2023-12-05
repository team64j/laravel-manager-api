<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class WorkspaceLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.settings_ui'))
                ->setIcon('fa fa-eye'),

            Tabs::make()
                ->setId('workspace')
                ->addTab('sidebar', 'Sidebar', null, 'p-6')
                ->addSlot('sidebar', [
                    [
                        'component' => 'AppTreeBuilder',
                        'model' => 'data.tree.data',
                    ],
                ])
                ->addTab('topmenu', 'Top menu', null, 'p-6')
                ->addSlot('topmenu', [
                    [
                        'component' => 'AppMenuBuilder',
                        'model' => 'data.topmenu.data',
                    ],
                ])
                ->addTab('dashboard', 'Dashboard', null, 'p-6'),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-eye';
    }
}
