<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteModule;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class ModuleLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-cube';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-cubes';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_module');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.modules');
    }

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

        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/modules',
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
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make(),

            Crumbs::make()->setData($breadcrumbs)
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setNew(
                    $this->title(),
                    '/modules/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(Lang::get('global.module_management_msg')),

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
                    'fa fa-list-alt',
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
                            true,
                            filter: true
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
                )
        ];
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('modules')
            ->setTitle(Lang::get('global.modules'))
            ->setIcon($this->iconList())
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
                        'default' => $this->icon(),
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
