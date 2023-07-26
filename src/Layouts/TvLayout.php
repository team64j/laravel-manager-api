<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\SiteTmplvar;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\CodeEditor;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Select;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Template;
use Team64j\LaravelManagerApi\Components\Textarea;
use Team64j\LaravelManagerApi\Components\Title;

class TvLayout extends Layout
{
    /**
     * @param SiteTmplvar|null $model
     *
     * @return array
     */
    public function default(SiteTmplvar $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'name' => 'Elements',
                        'params' => [
                            'element' => 'tvs',
                        ],
                        'close' => true,
                    ]
                )
                ->setSaveAnd()
                ->if(
                    $model->getKey(),
                    fn(ActionsButtons $actions) => $actions->setDelete()->setCopy()
                ),

            Title::make()
                ->setModel('name')
                ->setTitle(Lang::get('global.new_tmplvars'))
                ->setIcon('fa fa-list-alt')
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('tv')
                ->addTab('default', Lang::get('global.page_data_general'), null, 'px-4 py-8 flex flex-wrap')
                ->addSlot('default', [
                    Template::make()
                        ->setClass('flex flex-wrap md:basis-2/3 xl:basis-9/12 px-4 pb-0')
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
                        ->setClass('flex flex-wrap md:basis-1/3 xl:basis-3/12 w-full px-4 pb-0')
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
                ->addTab('settings', Lang::get('global.settings_properties'), null, 'px-4 py-8 flex flex-wrap')
                ->addTab('props', Lang::get('global.page_data_general'), null, 'px-4 py-8 flex flex-wrap')
                ->addTab('templates', Lang::get('global.templates'), null, 'px-4 py-8 flex flex-wrap')
                ->addTab('roles', Lang::get('global.role_management_title'), null, 'px-4 py-8 flex flex-wrap')
                ->addTab('permissions', Lang::get('global.access_permissions'), null, 'px-4 py-8 flex flex-wrap'),
        ];
    }

    /**
     * @param SiteTmplvar|null $model
     *
     * @return array
     */
    public function titleDefault(SiteTmplvar $model = null): array
    {
        return [
            'title' => $model->name ?: Lang::get('global.new_tmplvars'),
            'icon' => 'fa fa-list-alt',
        ];
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return [
            ActionsButtons::make()
                ->setAction('sort', Lang::get('global.template_tv_edit'), 'TvSort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.new_tmplvars'),
                    'Tv',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle(Lang::get('global.tmplvars'))
                ->setIcon('fa fa-list-alt')
                ->setHelp(Lang::get('global.tmplvars_management_msg')),

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
                    'tvs',
                    Panel::make()
                        ->setId('tvs')
                        ->setModel('data')
                        ->setData([])
                        ->setRoute('Tv')
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
                ),
        ];
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.tmplvars'),
            'icon' => 'fa fa-list-alt',
        ];
    }

    /**
     * @return array
     */
    public function sort(): array
    {
        return [
            ActionsButtons::make(['cancel', 'save'])
                ->setCancelTo([
                    'name' => 'Elements',
                    'params' => [
                        'element' => 'tvs',
                    ],
                    'close' => true,
                ]),

            Title::make()
                ->setTitle(Lang::get('global.template_tv_edit_title'))
                ->setIcon('fa fa-sort-numeric-asc'),

            Panel::make()
                ->setModel('data')
                ->setId('plugins')
                ->setClass('py-4')
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
     * @return array
     */
    public function titleSort(): array
    {
        return [
            'title' => Lang::get('global.template_tv_edit_title'),
            'icon' => 'fa fa-sort-numeric-asc',
        ];
    }
}
