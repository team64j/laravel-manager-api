<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class CategoryLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-object-group';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-object-group';
    }

    /**
     * @return string
     */
    public function iconSort(): string
    {
        return 'fa fa-sort-numeric-asc';
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return Lang::get('global.new_category');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.category_management');
    }

    /**
     * @param Category|null $model
     *
     * @return array
     */
    public function default(Category $model = null): array
    {
        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/categories',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('category')
                ->setTitle(Lang::get('global.new_category'))
                ->setIcon(self::icon())
                ->setId($model->getKey()),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            Actions::make()
                ->setAction('sort', Lang::get('global.cm_sort_categories'), '/categories/sort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.cm_add_new_category'),
                    '/categories/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Tabs::make()
                ->setId('elements')
                ->setHistory(true)
                ->isWatch()
                ->addTab(
                    'templates',
                    Lang::get('global.templates'),
                    'fa fa-newspaper',
                    '',
                    ['edit_template'],
                    route('manager.api.elements.templates'),
                )
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-list-alt',
                    '',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    route('manager.api.elements.tvs')
                )
                ->addTab(
                    'chunks',
                    Lang::get('global.htmlsnippets'),
                    'fa fa-th-large',
                    '',
                    ['edit_chunk'],
                    route('manager.api.elements.chunks')
                )
                ->addTab(
                    'snippets',
                    Lang::get('global.snippets'),
                    'fa fa-code',
                    '',
                    ['edit_snippet'],
                    route('manager.api.elements.snippets')
                )
                ->addTab(
                    'plugins',
                    Lang::get('global.plugins'),
                    'fa fa-plug',
                    '',
                    ['edit_plugin'],
                    route('manager.api.elements.plugins')
                )
                ->addTab(
                    'modules',
                    Lang::get('global.modules'),
                    'fa fa-cubes',
                    '',
                    ['edit_module'],
                    route('manager.api.elements.modules')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    '',
                    ['category_manager'],
                    route('manager.api.elements.categories')
                )
                ->addSlot(
                    'categories',
                    Panel::make()
                        ->setId('categories')
                        ->setModel('data')
                        ->setRoute('/categories/:id')
                        ->setHistory(true)
                        ->addColumn(
                            '#',
                            null,
                            ['width' => '3rem'],
                            false,
                            [],
                            [
                                [
                                    'icon' => 'fa fa-object-group fa-fw pointer-events-none',
                                    'noOpacity' => true,
                                ],
                            ]
                        )
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn(
                            'category',
                            Lang::get('global.cm_category_name'),
                            ['fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'rank',
                            Lang::get('global.cm_category_position'),
                            ['width' => '15rem', 'textAlign' => 'center'],
                            true
                        )
                ),
        ];
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            Actions::make()
                ->setCancelTo([
                    'path' => '/elements/categories',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.cm_sort_categories'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('categories')
                ->isDraggable('priority')
                ->addColumn(
                    '#',
                    '#',
                    ['width' => '5rem', 'textAlign' => 'center'],
                    false,
                    [],
                    [],
                    false,
                    'fa fa-bars fa-fw'
                )
                ->addColumn(
                    'id',
                    Lang::get('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                )
                ->addColumn('category', Lang::get('global.cm_category_name'), ['fontWeight' => 500])
                ->addColumn(
                    'rank',
                    Lang::get('global.cm_category_position'),
                    ['width' => '15rem', 'textAlign' => 'center']
                )
                ->isDraggable('rank'),
        ];
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('categories')
            ->setIcon($this->iconList())
            ->setTitle($this->titleList())
            ->setPermissions(['category_manager'])
            ->setRoute('/categories/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('categories')
                    ->setRoute('/categories/:id')
                    ->setUrl('/categories/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
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
                        'order' => 'category',
                    ])
            )
            ->toArray();
    }
}
