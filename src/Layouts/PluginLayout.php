<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\SitePlugin;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;

class PluginLayout extends Layout
{
    /**
     * @param SitePlugin|null $model
     *
     * @return array
     */
    public function default(SitePlugin $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'name' => 'Elements',
                        'params' => [
                            'element' => 'plugins',
                        ],
                        'close' => true,
                    ]
                )
                ->setSaveAnd()
                ->if(
                    $model->getKey(),
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                ),

            Title::make()
                ->setModel('name')
                ->setTitle(Lang::get('global.new_plugin'))
                ->setIcon('fa fa-plug')
                ->setId($model->getKey()),
        ];
    }

    /**
     * @param SitePlugin|null $model
     *
     * @return array
     */
    public function titleDefault(SitePlugin $model = null): array
    {
        return [
            'title' => $model->name ?: Lang::get('global.new_plugin'),
            'icon' => 'fa fa-plug',
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setAction('sort', Lang::get('global.plugin_priority'), 'PluginSort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.new_plugin'),
                    'Plugin',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.plugins'))
                ->setIcon('fa fa-plug')
                ->setHelp(Lang::get('global.plugin_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory('element')
                ->addTab('templates', Lang::get('global.templates'), 'fa fa-newspaper', 'py-4', ['edit_template'])
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-th-large',
                    'py-4',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']
                )
                ->addTab('chunks', Lang::get('global.htmlsnippets'), 'fa fa-th-large', 'py-4', ['edit_chunk'])
                ->addTab('snippets', Lang::get('global.snippets'), 'fa fa-code', 'py-4', ['edit_snippet'])
                ->addTab('plugins', Lang::get('global.plugins'), 'fa fa-plug', 'py-4', ['edit_plugin'])
                ->addTab('modules', Lang::get('global.modules'), 'fa fa-cubes', 'py-4', ['edit_module'])
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    'py-4',
                    ['category_manager']
                )
                ->addSlot(
                    'plugins',
                    Panel::make()
                        ->setId('plugins')
                        ->setModel('data')
                        ->setData([])
                        ->setRoute('Plugin')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-plug fa-fw"/>',
                                '<i class="fa fa-plug fa-fw" title="' .
                                Lang::get('global.locked') . '"><i class="fa fa-lock"/></i>',
                            ]
                        )
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn(
                            'name',
                            Lang::get('global.plugin_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true
                        )
                        ->addColumn('description', Lang::get('global.plugin_desc'))
                        //->addColumn('category', Lang::get('global.category_heading'), ['width' => '15rem'], true)
                        ->addColumn(
                            'locked',
                            Lang::get('global.locked'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                        ->addColumn(
                            'disabled',
                            Lang::get('global.disabled'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-green-600">' . Lang::get('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . Lang::get('global.yes') . '</span>',
                            ]
                        )
                        ->addColumn(
                            'actions',
                            Lang::get('global.onlineusers_action'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            false,
                            [],
                            [
                                'copy' => [
                                    'icon' => 'far fa-clone fa-fw hover:text-blue-500',
                                    'help' => Lang::get('global.duplicate'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                                'delete' => [
                                    'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                    'help' => Lang::get('global.delete'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                            ]
                        ),
                    ['edit_plugin']
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.plugins'),
            'icon' => 'fa fa-plug',
        ];
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            ActionsButtons::make(['cancel', 'save'])
                ->setCancelTo([
                    'name' => 'Elements',
                    'params' => [
                        'element' => 'plugins',
                    ],
                    'close' => true,
                ]),

            Title::make()
                ->setTitle(Lang::get('global.plugin_priority_title'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('plugins')
                ->setClass('py-4')
                ->addColumn(
                    '#',
                    '#',
                    ['width' => '5rem', 'textAlign' => 'center'],
                    false,
                    [],
                    [
                        'sortable' => [
                            'icon' => 'fa fa-bars fa-fw draggable-handle',
                            'noOpacity' => true,
                        ],
                    ]
                )
                ->addColumn(
                    'id',
                    Lang::get('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                )
                ->addColumn('name', Lang::get('global.plugin_name'), ['fontWeight' => 500])
                ->addColumn(
                    'priority',
                    Lang::get('global.tmplvars_rank'),
                    ['width' => '15rem', 'textAlign' => 'center']
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleSort(): array
    {
        return [
            'title' => Lang::get('global.plugin_priority_title'),
            'icon' => 'fa fa-sort-numeric-asc',
        ];
    }
}
