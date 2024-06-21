<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTmplvar;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\ActionsButtons;
use Team64j\LaravelManagerComponents\Breadcrumbs;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Main;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Template;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class TvLayout extends Layout
{
    /**
     * @param SiteTmplvar|null $model
     *
     * @return array
     */
    public function default(SiteTmplvar $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.tmplvars') . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/tvs?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancel(
                        Lang::get('global.cancel'),
                        [
                            'path' => '/elements/tvs',
                            'close' => true,
                        ]
                    )
                    ->when(
                        $model->getKey(),
                        fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                    )
                    ->setSaveAnd()
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setModel('name')
                    ->setTitle(Lang::get('global.new_tmplvars'))
                    ->setIcon('fa fa-list-alt')
                    ->setId($model->getKey())
            )
            ->setTabs(
                fn(Tabs $component) => $component
                    ->setId('tv')
                    ->addTab('default', Lang::get('global.page_data_general'))
                    ->addSlot('default', [
                        Template::make()
                            ->setClass('flex flex-wrap md:basis-2/3 xl:basis-9/12 md:pr-5 pb-0')
                            ->setSlot([
                                Input::make('name', Lang::get('global.tmplvars_name'))
                                    ->isRequired(),
                                Input::make('caption', Lang::get('global.tmplvars_caption')),
                                Textarea::make('description', Lang::get('global.tmplvars_description'))
                                    ->setRows(2),
                                CodeEditor::make(
                                    'elements',
                                    Lang::get('global.tmplvars_elements'),
                                    Lang::get('global.tmplvars_binding_msg')
                                )
                                    ->setRows(2),
                                CodeEditor::make(
                                    'default_text',
                                    Lang::get('global.tmplvars_default'),
                                    Lang::get('global.tmplvars_binding_msg')
                                )
                                    ->setRows(2),
                                Select::make('display', Lang::get('global.tmplvars_widget')),
                            ]),
                        Template::make()
                            ->setClass('flex flex-wrap md:basis-1/3 xl:basis-3/12 w-full pb-0')
                            ->setSlot([
                                Select::make('category', Lang::get('global.existing_category'))
                                    ->setUrl('/categories/select')
                                    ->setNew('')
                                    ->setData([
                                        [
                                            'key' => $model->category,
                                            'value' => $model->categories
                                                ? $model->categories->category
                                                : Lang::get(
                                                    'global.no_category'
                                                ),
                                            'selected' => true,
                                        ],
                                    ]),
                                Select::make('type', Lang::get('global.tmplvars_type'))
                                    ->setUrl('/tvs/types')
                                    ->setData([
                                        [
                                            'key' => $model->type,
                                            'value' => $model->getStandardTypes()[$model->type] ?? $model->type,
                                        ],
                                    ]),
                                Input::make('rank', Lang::get('global.tmplvars_rank')),
                                Checkbox::make('locked', Lang::get('global.lock_tmplvars_msg'))
                                    ->setCheckedValue(1, 0),
                            ]),
                    ])
                    ->addTab('settings', Lang::get('global.settings_properties'))
                    ->addTab('props', Lang::get('global.page_data_general'))
                    ->addTab('templates', Lang::get('global.templates'))
                    ->addTab('roles', Lang::get('global.role_management_title'))
                    ->addTab('permissions', Lang::get('global.access_permissions'))
            )
            ->setBreadcrumbs(
                fn(Breadcrumbs $component) => $component->setData($breadcrumbs)
            )
            ->toArray();
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setAction('sort', Lang::get('global.template_tv_edit'), '/tvs/sort', null, 'fa fa-sort')
                    ->setNew(
                        Lang::get('global.new_tmplvars'),
                        '/tvs/new',
                        'btn-green',
                        'fa fa-plus'
                    )
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setTitle(Lang::get('global.tmplvars'))
                    ->setIcon('fa fa-list-alt')
                    ->setHelp(Lang::get('global.tmplvars_management_msg'))
            )
            ->setTabs(
                fn(Tabs $component) => $component
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
                        'tvs',
                        Panel::make()
                            ->setId('tvs')
                            ->setModel('data')
                            ->setRoute('/tvs/:id')
                            ->setHistory(true)
                            ->addColumn(
                                ['#', 'locked'],
                                null,
                                ['width' => '3rem'],
                                false,
                                [
                                    '<i class="fa fa-list-alt fa-fw"/>',
                                    '<i class="fa fa-list-alt fa-fw" title="' .
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
                                Lang::get('global.tmplvars_name'),
                                ['width' => '20rem', 'fontWeight' => 500],
                                true
                            )
                            ->addColumn('caption', Lang::get('global.tmplvars_caption'), [], true)
                            ->addColumn('type', Lang::get('global.tmplvars_type'), ['width' => '10rem'])
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
                                'rank',
                                Lang::get('global.tmplvars_rank'),
                                ['width' => '15rem', 'textAlign' => 'center'],
                                true
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
                        ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']
                    )
            )
            ->toArray();
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-list-alt';
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            ActionsButtons::make()
                ->setCancelTo([
                    'path' => '/elements/tvs',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.template_tv_edit_title'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('plugins')
                ->isDraggable('rank')
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
                ->addColumn('name', Lang::get('global.plugin_name'), ['fontWeight' => 500])
                ->addColumn('caption', Lang::get('global.tmplvars_caption'))
                ->addColumn(
                    'rank',
                    Lang::get('global.tmplvars_rank'),
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
            ->setId('tvs')
            ->setTitle(Lang::get('global.tmplvars'))
            ->setIcon('fa fa-list-alt')
            ->setPermissions(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'])
            ->setRoute('/tvs/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('tvs')
                    ->setRoute('/tvs/:id')
                    ->setUrl('/tvs/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-list-alt',
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
