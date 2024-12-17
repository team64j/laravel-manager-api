<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SitePlugin;
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

class PluginLayout extends Layout
{
    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? __('global.new_plugin');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.plugins');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-plug';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-plug';
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
    public function titleSort(): string
    {
        return __('global.plugin_priority_title');
    }

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
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to' => '/elements/plugins?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    __('global.cancel'),
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
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('plugin')
                ->setClass('px-4 pb-4')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Template::make()
                            ->setClass('flex flex-wrap md:basis-2/3 xl:basis-9/12 p-5')
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
                            'plugincode',
                            __('global.plugin_code'),
                            null,
                            'mx-5'
                        )
                            ->setRows(25)
                            ->setLanguage('php'),
                    ],
                )
                ->addTab(
                    'events',
                    __('global.settings_events'),
                    slot: [
                        Panel::make()
                            ->setSlotTop('<div class="p-5 w-full">' . __('global.plugin_event_msg') . '</div>')
                            ->setUrl('/plugins/events')
                            ->setModel('events')
                            ->setColumns([
                                'checked',
                                'name',
                            ]),
                    ]
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
                ->setAction('sort', __('global.plugin_priority'), '/plugins/sort', null, 'fa fa-sort')
                ->setNew(
                    $this->title(),
                    '/plugins/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->icon())
                ->setHelp(__('global.plugin_management_msg')),

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
                            __('global.plugin_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.plugin_desc')
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
                ->setTitle($this->titleSort())
                ->setIcon($this->iconSort()),

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
                    __('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                )
                ->addColumn(
                    'name',
                    __('global.plugin_name'),
                    ['fontWeight' => 500]
                )
                ->addColumn(
                    'priority',
                    __('global.tmplvars_rank'),
                    ['width' => '15rem', 'textAlign' => 'center']
                ),
        ];
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('plugins')
            ->setTitle($this->titleList())
            ->setIcon($this->iconList())
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
                                'title' => __('global.new_plugin'),
                                'to' => [
                                    'path' => '/plugins/0',
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
