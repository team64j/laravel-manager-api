<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SitePlugin;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class PluginLayout extends Layout
{
    /**
     * @param SitePlugin|null $model
     *
     * @return array
     */
    public function default(SitePlugin $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.plugins') . ': ' .
                    ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/plugins?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/plugins',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('name')
                ->setTitle(Lang::get('global.new_plugin'))
                ->setIcon('fa fa-plug')
                ->setId($model->getKey()),

            Tabs::make(),

            Crumbs::make()->setData($breadcrumbs),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setAction('sort', Lang::get('global.plugin_priority'), '/plugins/sort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.new_plugin'),
                    '/plugins/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.plugins'))
                ->setIcon('fa fa-plug')
                ->setHelp(Lang::get('global.plugin_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory(true)
                ->isWatch()
                ->addTab(
                    'templates',
                    Lang::get('global.templates'),
                    'fa fa-newspaper',
                    'py-4',
                    ['edit_template'],
                    route: route('manager.api.elements.templates')
                )
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-th-large',
                    'py-4',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    route: route('manager.api.elements.tvs')
                )
                ->addTab(
                    'chunks',
                    Lang::get('global.htmlsnippets'),
                    'fa fa-th-large',
                    'py-4',
                    ['edit_chunk'],
                    route: route('manager.api.elements.chunks')
                )
                ->addTab(
                    'snippets',
                    Lang::get('global.snippets'),
                    'fa fa-code',
                    'py-4',
                    ['edit_snippet'],
                    route: route('manager.api.elements.snippets')
                )
                ->addTab(
                    'plugins',
                    Lang::get('global.plugins'),
                    'fa fa-plug',
                    'py-4',
                    ['edit_plugin'],
                    route: route('manager.api.elements.plugins')
                )
                ->addTab(
                    'modules',
                    Lang::get('global.modules'),
                    'fa fa-cubes',
                    'py-4',
                    ['edit_module'],
                    route: route('manager.api.elements.modules')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    'py-4',
                    ['category_manager'],
                    route: route('manager.api.elements.categories')
                )
                ->addSlot(
                    'plugins',
                    Panel::make()
                        ->setId('plugins')
                        ->setModel('data')
                        ->setRoute('/plugins/:id')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-plug fa-fw"/>',
                                '<i class="fa fa-plug fa-fw" data-tooltip="' .
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
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            Lang::get('global.plugin_desc')
                        )
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
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-plug';
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            Actions::make()
                ->setCancelTo([
                    'path' => '/elements/plugins',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.plugin_priority_title'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('plugins')
                ->isDraggable('priority')
                ->addColumn(
                    '#',
                    '#',
                    ['width' => '5rem', 'textAlign' => 'center'],
                    icon: 'fa fa-bars fa-fw'
                )
                ->addColumn(
                    'id',
                    Lang::get('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                )
                ->addColumn(
                    'name',
                    Lang::get('global.plugin_name'),
                    ['fontWeight' => 500]
                )
                ->addColumn(
                    'priority',
                    Lang::get('global.tmplvars_rank'),
                    ['width' => '15rem', 'textAlign' => 'center']
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIconSort(): string
    {
        return 'fa fa-sort-numeric-asc';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('plugins')
            ->setTitle(Lang::get('global.plugins'))
            ->setIcon('fa fa-plug')
            ->setPermissions('edit_plugin')
            ->setRoute('/plugins/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('plugins')
                    ->setRoute('/plugins/:id')
                    ->setUrl('/plugins/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
                        'deleted' => 'disabled',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-plug',
                    ])
                    ->setMenu([
                        'actions' => [
                            [
                                'icon' => 'fa fa-refresh',
                                'click' => 'update',
                                'loader' => true,
                            ],
                            [
                                'component' => 'search',
                            ],
                        ],
                    ])
                    ->setSettings([
                        'parent' => -1,
                    ])
            )
            ->toArray();
    }
}
