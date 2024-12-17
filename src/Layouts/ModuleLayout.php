<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\DocumentgroupName;
use EvolutionCMS\Models\SiteModule;
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
    public function default(SiteModule $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => __('global.modules') . ': ' .
                    ($category->category ?? __('global.no_category')),
                'to' => '/elements/modules?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    __('global.cancel'),
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

            Tabs::make()
                ->setId('module')
                ->setClass('px-4 pb-4')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Template::make()
                            ->setClass('flex flex-wrap md:basis-2/3 xl:basis-9/12 p-5')
                            ->setSlot([
                                Input::make('name', __('global.module_name'))->setClass('mb-3')->isRequired(),
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
                            ->setClass('flex flex-wrap md:basis-1/3 xl:basis-3/12 w-full p-5 md:!pl-2')
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
                            'modulecode',
                            __('global.module_code'),
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
                    class: 'p-5',
                    slot: [
                        Input::make(
                            'guid',
                            'GUID',
                            __('global.import_params_msg'),
                            'mb-3'
                        ),
                        Checkbox::make(
                            'enable_sharedparams',
                            __('global.enable_sharedparams'),
                            __('global.enable_sharedparams_msg'),
                            'mb-5'
                        ),
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
                            class: 'flex-col p-5',
                            slot: [
                                __('global.access_permissions_docs_message') . '<br/><br/>',

                                Checkbox::make()
                                    ->setModel('data.is_module_group')
                                    ->setLabel(__('global.all_doc_groups'))
                                    ->setCheckedValue(true, false)
                                    ->setRelation('data.document_groups', [], [], true)
                                    ->setClass('mb-3'),

                                Checkbox::make()
                                    ->setModel('data.module_groups')
                                    ->setLabel(__('global.access_permissions_resource_groups'))
                                    ->setData(
                                        DocumentgroupName::all()
                                            ->map(fn(DocumentgroupName $group) => [
                                                'key' => $group->getKey(),
                                                'value' => $group->name,
                                            ])
                                            ->toArray()
                                    )
                                    ->setRelation('data.is_module_group', false, true)
                                    ->setClass('mb-3'),
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
                            'locked',
                            __('global.locked'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            true,
                            [
                                0 => '<span class="text-green-600">' . __('global.no') . '</span>',
                                1 => '<span class="text-rose-600">' . __('global.yes') . '</span>',
                            ]
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
                        ->addColumn(
                            'actions',
                            __('global.onlineusers_action'),
                            ['width' => '10rem', 'textAlign' => 'center'],
                            false,
                            [],
                            [
                                'copy' => [
                                    'icon' => 'far fa-clone fa-fw hover:text-blue-500',
                                    'help' => __('global.duplicate'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
                                'delete' => [
                                    'icon' => 'fa fa-trash fa-fw hover:text-rose-600',
                                    'help' => __('global.delete'),
                                    'helpFit' => true,
                                    'noOpacity' => true,
                                ],
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
                                'icon' => 'fa fa-circle-plus',
                                'title' => __('global.new_module'),
                                'to' => [
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
