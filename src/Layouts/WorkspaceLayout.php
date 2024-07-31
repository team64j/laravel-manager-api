<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;

class WorkspaceLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-eye';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return Lang::get('global.settings_ui');
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Actions::make()
                ->setCancel()
                ->setSave(),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

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
}
