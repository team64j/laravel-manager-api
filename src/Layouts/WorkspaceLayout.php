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
        $data[] = ActionsButtons::make()
            ->setCancel()
            ->setSave();

        $data[] = Title::make()
            ->setTitle(Lang::get('global.settings_ui'))
            ->setIcon('fa fa-eye');

        $data[] = Tabs::make()
            ->setId('workspace')
            ->addTab('sidebar', 'Sidebar', null, 'p-6')
            ->addSlot('sidebar', [
                Checkbox::make(
                    'data.sidebar.templates',
                    Lang::get('global.disable') . ' "' . Lang::get('global.templates') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.tvs',
                    Lang::get('global.disable') . ' "' . Lang::get('global.tmplvars') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.chunks',
                    Lang::get('global.disable') . ' "' . Lang::get('global.htmlsnippets') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.snippets',
                    Lang::get('global.disable') . ' "' . Lang::get('global.snippets') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.plugins',
                    Lang::get('global.disable') . ' "' . Lang::get('global.plugins') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.modules',
                    Lang::get('global.disable') . ' "' . Lang::get('global.modules') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.categories',
                    Lang::get('global.disable') . ' "' . Lang::get('global.category_management') . '"'
                )
                    ->setCheckedValue(0, 1),

                Checkbox::make(
                    'data.sidebar.files',
                    Lang::get('global.disable') . ' "' . Lang::get('global.files_files') . '"'
                )
                    ->setCheckedValue(0, 1),
            ])
            ->addTab('topmenu', 'Top menu', null, 'p-6')
            ->addSlot('topmenu', [
                [
                    'component' => 'EvoMenuBuilder',
                    'model' => 'data.topmenu.data',
                ],
            ])
            ->addTab('dashboard', 'Dashboard', null, 'p-6');

        return $data;
    }

    /**
     * @return array
     */
    public function titleDefault(): array
    {
        return [
            'title' => Lang::get('global.settings_ui'),
            'icon' => 'fa fa-eye',
        ];
    }
}
