<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

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
        return __('global.settings_ui');
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
                ->setClass('px-4 pb-4')
                ->addTab(
                    'sidebar',
                    'Sidebar',
                    class: 'p-5',
                    slot: [
                        'component' => 'AppTreeBuilder',
                        'model' => 'data.tree.data',
                    ],
                )
                ->addTab(
                    'topmenu',
                    'Top menu',
                    class: 'p-5',
                    slot: [
                        'component' => 'AppMenuBuilder',
                        'model' => 'data.topmenu.data',
                    ],
                )
                ->addTab(
                    'dashboard',
                    'Dashboard',
                    class: 'p-6',
                    slot: []
                ),
        ];
    }
}
