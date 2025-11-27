<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteHtmlSnippet;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class ChunkLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.new_htmlsnippet');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.htmlsnippets');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-th-large';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-th-large';
    }

    /**
     * @param SiteHtmlSnippet|null $model
     *
     * @return array
     */
    public function default(?SiteHtmlSnippet $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id'    => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to'    => '/elements/chunks?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            GlobalTab::make()
                ->setTitle($model->name ?? $this->title())
                ->setIcon($this->icon()),

            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/chunks',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make('name')
                ->setTitle($this->title())
                ->setHelp(__('global.htmlsnippet_msg'))
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('chunk')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25rem')
                            ->addArea([
                                Input::make('name')
                                    ->setLabel(__('global.tmplvars_name'))
                                    ->isRequired()
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),
                                Textarea::make('description')
                                    ->setLabel(__('global.tmplvars_description'))
                                    ->setRows(2),
                            ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 3'])
                            ->addArea([
                                Select::make('category')
                                    ->setLabel(__('global.existing_category'))
                                    ->setUrl('/categories/select')
                                    ->setNew('')
                                    ->setData([
                                        [
                                            'key'      => $model->category,
                                            'value'    => $model->categories
                                                ? $model->categories->category
                                                : __(
                                                    'global.no_category'
                                                ),
                                            'selected' => true,
                                        ],
                                    ])
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('disabled')
                                    ->setLabel(__('global.disabled'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('locked')
                                    ->setLabel(__('global.lock_tmplvars_msg'))
                                    ->setCheckedValue(1, 0),
                            ], ['sm' => '2', 'xl' => '1 / 3 / 1 / 3'])
                            ->addArea([
                                CodeEditor::make('snippet')
                                    ->setLabel(__('global.chunk_code'))
                                    ->setRows(25)
                                    ->setLanguage('html'),
                            ], ['sm' => '3', 'xl' => '2 / 1 / 2 / 4']),
                    ],
                ),

            Crumbs::make()->setData($breadcrumbs),
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->icon()),

            Actions::make()
                ->setNew(
                    $this->title(),
                    '/chunks/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.htmlsnippet_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory(true)
                ->isWatch()
                ->addTab(
                    'templates',
                    __('global.templates'),
                    'fa fa-newspaper',
                    '',
                    ['edit_template'],
                    route('manager.api.elements.templates'),
                )
                ->addTab(
                    'tvs',
                    __('global.tmplvars'),
                    'fa fa-list-alt',
                    '',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    route('manager.api.elements.tvs')
                )
                ->addTab(
                    'chunks',
                    __('global.htmlsnippets'),
                    'fa fa-th-large',
                    '',
                    ['edit_chunk'],
                    route('manager.api.elements.chunks')
                )
                ->addTab(
                    'snippets',
                    __('global.snippets'),
                    'fa fa-code',
                    '',
                    ['edit_snippet'],
                    route('manager.api.elements.snippets')
                )
                ->addTab(
                    'plugins',
                    __('global.plugins'),
                    'fa fa-plug',
                    '',
                    ['edit_plugin'],
                    route('manager.api.elements.plugins')
                )
                ->addTab(
                    'modules',
                    __('global.modules'),
                    'fa fa-cubes',
                    '',
                    ['edit_module'],
                    route('manager.api.elements.modules')
                )
                ->addTab(
                    'categories',
                    __('global.category_management'),
                    'fa fa-object-group',
                    '',
                    ['category_manager'],
                    route('manager.api.elements.categories')
                )
                ->addSlot(
                    'chunks',
                    Panel::make('data')
                        ->setId('chunks')
                        ->setRoute('/chunks/:id')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-th-large fa-fw"/>',
                                '<i class="fa fa-th-large fa-fw" title="' .
                                __('global.locked') . '"><i class="fa fa-lock"/></i>',
                            ]
                        )
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn(
                            'name',
                            __('global.htmlsnippet_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.htmlsnippet_desc')
                        )
                        ->addColumn(
                            'disabled',
                            __('global.disabled'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-sucess">' . __('global.no') . '</span>',
                                1 => '<span class="text-error">' . __('global.yes') . '</span>',
                            ]
                        )
                ),
        ];
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('chunks')
            ->setTitle($this->titleList())
            ->setIcon($this->iconList())
            ->setPermissions('edit_chunk')
            ->setRoute('/chunks/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('chunks')
                    ->setRoute('/chunks/:id')
                    ->setUrl('/chunks/tree')
                    ->isCategory()
                    ->setAppends(['id'])
                    ->setAliases([
                        'locked' => 'locked:1',
                        'muted'  => 'disabled:1',
                    ])
                    ->setIcons([
                        'default' => $this->icon(),
                    ])
                    ->setMenu([
                        'actions' => [
                            [
                                'icon'   => 'fa fa-refresh',
                                'click'  => 'update',
                                'loader' => true,
                            ],
                            [
                                'icon'  => 'fa fa-circle-plus',
                                'title' => __('global.new_htmlsnippet'),
                                'to'    => [
                                    'path' => '/chunks/0',
                                ],
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
