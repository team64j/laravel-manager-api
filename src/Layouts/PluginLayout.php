<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SitePlugin;
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
    public function title(?string $value = null): string
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
    public function default(?SitePlugin $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id'    => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to'    => '/elements/plugins?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/plugins',
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
                ->setId('plugin')
                ->addTab(
                    'general',
                    __('global.page_data_general'),
                    slot: [
                        Template::make()
                            ->setSlot([
                                Input::make('name')
                                    ->setLabel(__('global.tmplvars_name'))
                                    ->isRequired(),
                                Textarea::make('description')
                                    ->setLabel(__('global.tmplvars_description'))
                                    ->setRows(2),
                                Checkbox::make('analyze')
                                    ->setLabel(__('global.parse_docblock'))
                                    ->setHelp(__('global.parse_docblock_msg'))
                                    ->setCheckedValue(1, 0),
                            ]),
                        Template::make()
                            ->setSlot([
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
                                    ]),
                                Checkbox::make('disabled')
                                    ->setLabel(__('global.disabled'))
                                    ->setCheckedValue(1, 0),
                                Checkbox::make('locked')
                                    ->setLabel(__('global.lock_tmplvars_msg'))
                                    ->setCheckedValue(1, 0),
                            ]),
                        CodeEditor::make('plugincode')
                            ->setLabel(__('global.plugin_code'))
                            ->setRows(25)
                            ->setLanguage('php'),
                    ],
                )
                ->addTab(
                    'events',
                    __('global.settings_events'),
                    slot: [
                        Panel::make('events')
                            ->setSlotTop('<div class="p-5 w-full">' . __('global.plugin_event_msg') . '</div>')
                            ->setUrl('/plugins/events')
                            ->addColumn(
                                'checked',
                                style: ['width' => '1%'],
                                selectable: true,
                                component: Checkbox::make('tvs')->setKeyValue('id')
                            )
                            ->addColumn(
                                'name'
                            ),
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
                    Panel::make('data')
                        ->setId('plugins')
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
    public function sort(): array
    {
        return [
            Actions::make()
                ->setCancelTo([
                    'path'  => '/elements/plugins',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle($this->titleSort())
                ->setIcon($this->iconSort()),

            Panel::make('data')
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
                                'title' => __('global.new_plugin'),
                                'to'    => [
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
