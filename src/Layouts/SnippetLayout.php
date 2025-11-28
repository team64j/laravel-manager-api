<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteSnippet;
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

class SnippetLayout extends Layout
{
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_snippet');
    }

    public function titleList(): string
    {
        return __('global.snippets');
    }

    public function icon(): string
    {
        return 'fa fa-code';
    }

    public function iconList(): string
    {
        return 'fa fa-code';
    }

    public function default(?SiteSnippet $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id'    => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to'    => '/elements/snippets?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            GlobalTab::make()
                ->setTitle($this->title($model->name))
                ->setIcon($this->icon()),

            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/snippets',
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
                ->setHelp(__('global.snippet_msg'))
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('snippet')
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
                                    ->setRows(2)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('analyze')
                                    ->setLabel(__('global.parse_docblock'))
                                    ->setHelp(__('global.parse_docblock_msg'))
                                    ->setCheckedValue(1, 0),
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
                                    ->setLanguage('php'),
                            ], ['sm' => '3', 'xl' => '2 / 1 / 2 / 4']),
                    ],
                )
                ->addTab(
                    'settings',
                    __('global.settings_properties'),
                    slot: CodeEditor::make('properties')
                        ->setLanguage('json')
                        ->isFullSize()
                ),

            Crumbs::make()->setData($breadcrumbs),
        ];
    }

    public function list(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Actions::make()
                ->setNew(
                    $this->title(),
                    '/snippets/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.snippet_management_msg')),

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
                    'snippets',
                    Panel::make('data')
                        ->setId('snippets')
                        ->setRoute('/snippets/:id')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                '<i class="fa fa-code fa-fw"/>',
                                '<i class="fa fa-code fa-fw" title="' .
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
                            __('global.snippet_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.snippet_desc')
                        )
                        ->addColumn(
                            'disabled',
                            __('global.disabled'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-success">' . __('global.no') . '</span>',
                                1 => '<span class="text-error">' . __('global.yes') . '</span>',
                            ]
                        )
                ),
        ];
    }

    public function tree(): array
    {
        return Tab::make()
            ->setId('snippets')
            ->setTitle($this->titleList())
            ->setIcon($this->icon())
            ->setPermissions('edit_snippet')
            ->setRoute('/snippets/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('snippets')
                    ->setRoute('/snippets/:id')
                    ->setUrl('/snippets/tree')
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
                                'title' => __('global.new_snippet'),
                                'to'    => [
                                    'path' => '/snippets/0',
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
