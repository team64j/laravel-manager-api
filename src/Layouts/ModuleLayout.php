<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteModule;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Breadcrumbs;
use Team64j\LaravelManagerApi\Components\Main;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tab;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;
use Team64j\LaravelManagerApi\Components\Tree;

class ModuleLayout extends Layout
{
    /**
     * @param SiteModule|null $model
     *
     * @return array
     */
    public function default(SiteModule $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.modules') . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/modules?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancel(
                        Lang::get('global.cancel'),
                        [
                            'path' => '/elements/modules',
                            'close' => true,
                        ]
                    )
                    ->when(
                        $model->getKey(),
                        fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                    )
                    ->setSaveAnd()
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setModel('name')
                    ->setTitle(Lang::get('global.new_module'))
                    ->setIcon('fa fa-cube')
                    ->setId($model->getKey())
            )
            ->setTabs(
                fn(Tabs $component) => $component
            )
            ->setBreadcrumbs(
                fn(Breadcrumbs $component) => $component->setData($breadcrumbs)
            )
            ->toArray();
    }

    /**
     * @param SiteModule|null $model
     *
     * @return array
     */
    public function default1(SiteModule $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/modules',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('name')
                ->setTitle(Lang::get('global.new_module'))
                ->setIcon('fa fa-cube')
                ->setId($model->getKey()),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setNew(
                    Lang::get('global.new_module'),
                    '/modules/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.modules'))
                ->setIcon('fa fa-cubes')
                ->setHelp(Lang::get('global.module_management_msg')),

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
                    'modules',
                    Panel::make()
                        ->setId('modules')
                        ->setModel('data')
                        ->setRoute('/modules/:id')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-cube fa-fw"/>',
                                '<i class="fa fa-cube fa-fw" title="' .
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
                            Lang::get('global.module_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true
                        )
                        ->addColumn(
                            'description',
                            Lang::get('global.module_desc')
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
                    ['edit_module']
                ),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-cubes';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('modules')
            ->setTitle(Lang::get('global.modules'))
            ->setIcon('fa fa-cubes')
            ->setPermissions('edit_module')
            ->setRoute('/modules/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('modules')
                    ->setRoute('/modules/:id')
                    ->setUrl('/modules/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
                        'deleted' => 'disabled',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-cubes',
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
