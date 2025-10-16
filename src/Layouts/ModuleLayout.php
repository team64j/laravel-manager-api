<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\DocumentgroupName;
use Team64j\LaravelManagerApi\Models\SiteModule;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Textarea;
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
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_module');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.modules');
    }

    /**
     * @param SiteModule|null $model
     *
     * @return array
     */
    public function default(?SiteModule $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id'    => $category->getKey() ?? 0,
                'title' => __('global.modules') . ': ' .
                    ($category->category ?? __('global.no_category')),
                'to'    => '/elements/modules?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/modules',
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
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('module')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25rem')
                            ->addArea([
                                Input::make('name')
                                    ->setLabel(__('global.module_name'))
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
                            ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 2'])
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
                            ], ['sm' => '2', 'xl' => '1 / 2 / 1 / 2'])
                            ->addArea([
                                CodeEditor::make('modulecode')
                                    ->setLabel(__('global.module_code'))
                                    ->setRows(25)
                                    ->setLanguage('php'),
                            ], ['sm' => '3', 'xl' => '2 / 1 / 2 / 3']),
                    ],
                )
                ->addTab(
                    'settings',
                    __('global.settings_properties'),
                    slot: [
                        Input::make('guid')
                            ->setLabel('GUID')
                            ->setHelp(__('global.import_params_msg')),
                        Checkbox::make('enable_sharedparams')
                            ->setLabel(__('global.enable_sharedparams'))
                            ->setHelp(__('global.enable_sharedparams_msg')),
                        CodeEditor::make('properties')
                            ->setRows(25)
                            ->setLanguage('json'),
                    ]
                )
                ->when(
                    config('global.use_udperms'),
                    fn(Tabs $tabs) => $tabs
                        ->addTab(
                            'permissions',
                            __('global.access_permissions'),
                            slot: [
                                __('global.access_permissions_docs_message') . '<br/><br/>',

                                Checkbox::make('data.is_module_group')
                                    ->setLabel(__('global.all_doc_groups'))
                                    ->setCheckedValue(true, false)
                                    ->setRelation('data.document_groups', [], [], true),

                                Checkbox::make('data.module_groups')
                                    ->setLabel(__('global.access_permissions_resource_groups'))
                                    ->setData(
                                        DocumentgroupName::all()
                                            ->map(fn(DocumentgroupName $group) => [
                                                'key'   => $group->getKey(),
                                                'value' => $group->name,
                                            ])
                                            ->toArray()
                                    )
                                    ->setRelation('data.is_module_group', false, true),
                            ]
                        )
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
                    '/modules/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.module_management_msg')),

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
                    'modules',
                    Panel::make('data')
                        ->setId('modules')
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
                            __('global.module_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.module_desc')
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

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('modules')
            ->setTitle(__('global.modules'))
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
                                'title' => __('global.new_module'),
                                'to'    => [
                                    'path' => '/modules/0',
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
