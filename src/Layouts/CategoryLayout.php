<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Tab;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Title;
use Team64j\LaravelManagerApi\Components\Tree;

class CategoryLayout extends Layout
{
    /**
     * @param Category|null $model
     *
     * @return array
     */
    public function default(Category $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'name' => 'Elements',
                        'params' => [
                            'element' => 'categories',
                        ],
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('category')
                ->setTitle(Lang::get('global.new_category'))
                ->setIcon('fa fa-object-group')
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
                ->setAction('sort', Lang::get('global.cm_sort_categories'), 'CategorySort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.cm_add_new_category'),
                    'Category',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.category_management'))
                ->setIcon('fa fa-object-group'),

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
                    'categories',
                    Panel::make()
                        ->setId('categories')
                        ->setModel('data')
                        ->setData([])
                        ->setRoute('Category')
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
                            true
                        )
                        ->addColumn(
                            'rank',
                            Lang::get('global.cm_category_position'),
                            ['width' => '15rem', 'textAlign' => 'center'],
                            true
                        ),
                    ['category_manager']
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.category_management'),
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-object-group';
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            ActionsButtons::make()
                ->setCancelTo([
                    'name' => 'Elements',
                    'params' => [
                        'element' => 'categories',
                    ],
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.cm_sort_categories'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('categories')
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
                ->addColumn('category', Lang::get('global.cm_category_name'), ['fontWeight' => 500])
                ->addColumn(
                    'rank',
                    Lang::get('global.cm_category_position'),
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
            ->setId('categories')
            ->setIcon('fa fa-object-group')
            ->setTitle(Lang::get('global.category_management'))
            ->setPermissions(['category_manager'])
            ->setRoute(['Category'])
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('categories')
                    ->setRoute('Category')
                    ->setUrl('/categories/tree?order=category')
                    ->isCategory()
                    ->setAliases([
                        'category' => 'title',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-object-group',
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
            )
            ->toArray();
    }
}
