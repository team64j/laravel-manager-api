<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteSnippet;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Template;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class SnippetLayout extends Layout
{
    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? __('global.new_snippet');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.snippets');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-code';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-code';
    }

    /**
     * @param SiteSnippet|null $model
     *
     * @return array
     */
    public function default(SiteSnippet $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to' => '/elements/snippets?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path' => '/elements/snippets',
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
                ->setHelp(__('global.snippet_msg'))
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('snippet')
                ->setClass('px-4 pb-4')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Template::make()
                            ->setClass('flex flex-wrap grow p-5 lg:w-0')
                            ->setSlot([
                                Input::make('name', __('global.tmplvars_name'))->setClass('mb-3')->isRequired(),
                                Textarea::make('description', __('global.tmplvars_description'))
                                    ->setClass('mb-3')
                                    ->setRows(2),
                                Checkbox::make(
                                    'analyze',
                                    __('global.parse_docblock'),
                                    __('global.parse_docblock_msg')
                                )
                                    ->setCheckedValue(1, 0),
                            ]),
                        Template::make()
                            ->setClass('flex flex-wrap grow p-5 lg:max-w-96')
                            ->setSlot([
                                Select::make('category', __('global.existing_category'))
                                    ->setClass('mb-3')
                                    ->setUrl('/categories/select')
                                    ->setNew('')
                                    ->setData([
                                        [
                                            'key' => $model->category,
                                            'value' => $model->categories
                                                ? $model->categories->category
                                                : __(
                                                    'global.no_category'
                                                ),
                                            'selected' => true,
                                        ],
                                    ]),
                                Checkbox::make('disabled', __('global.disabled'))
                                    ->setClass('mb-3')
                                    ->setCheckedValue(1, 0),
                                Checkbox::make('locked', __('global.lock_tmplvars_msg'))
                                    ->setClass('mb-3')
                                    ->setCheckedValue(1, 0),
                            ]),
                        CodeEditor::make(
                            'snippet',
                            __('global.chunk_code'),
                            null,
                            'mx-5'
                        )
                            ->setRows(25)
                            ->setLanguage('php'),
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

    /**
     * @return array
     */
    public function list(): array
    {
        return [
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
                ->setClass('px-4 pb-4')
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
                    Panel::make()
                        ->setId('snippets')
                        ->setModel('data')
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
                                0 => '<span class="text-green-600">' . __('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . __('global.yes') . '</span>',
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
                        'muted' => 'disabled:1',
                    ])
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
                                'icon' => 'fa fa-circle-plus',
                                'title' => __('global.new_snippet'),
                                'to' => [
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
