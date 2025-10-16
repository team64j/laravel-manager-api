<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\DocumentgroupName;
use Team64j\LaravelManagerApi\Models\SiteTmplvar;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Number;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class TvLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-list-alt';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-list-alt';
    }

    /**
     * @return string
     */
    public function iconSort(): string
    {
        return 'fa fa-sort-numeric-asc';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_tmplvars');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.tmplvars');
    }

    /**
     * @return string
     */
    public function titleSort(): string
    {
        return __('global.template_tv_edit_title');
    }

    /**
     * @param SiteTmplvar|null $model
     *
     * @return array
     */
    public function default(?SiteTmplvar $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id'    => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? __('global.no_category')),
                'to'    => '/elements/tvs?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            GlobalTab::make()
                ->setIcon($this->icon())
                ->setTitle($this->title($model->name)),

            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/tvs',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $actions) => $actions->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make('data.attributes.name')
                ->setTitle($this->title())
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('tv')
                ->addTab(
                    'default',
                    __('global.page_data_general'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25rem')
                            ->addArea([
                                Input::make('data.attributes.name')
                                    ->setLabel(__('global.tmplvars_name'))
                                    ->isRequired()
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Input::make('data.attributes.caption')
                                    ->setLabel(__('global.tmplvars_caption'))
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Textarea::make('data.attributes.description')
                                    ->setLabel(__('global.tmplvars_description'))
                                    ->setRows(2)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                CodeEditor::make('data.attributes.elements')
                                    ->setLabel(__('global.tmplvars_elements'))
                                    ->setHelp(__('global.tmplvars_binding_msg'))
                                    ->setRows(2)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                CodeEditor::make('data.attributes.default_text')
                                    ->setLabel(__('global.tmplvars_default'))
                                    ->setHelp(__('global.tmplvars_binding_msg'))
                                    ->setRows(2),
                            ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 2'])
                            ->addArea([
                                Select::make('data.attributes.category')
                                    ->setLabel(__('global.existing_category'))
                                    ->setUrl('/categories/select')
                                    ->setNew('')
                                    ->setData([
                                        [
                                            'key'      => $model->category,
                                            'value'    => $model->categories
                                                ? $model->categories->category
                                                : __('global.no_category'),
                                            'selected' => true,
                                        ],
                                    ])
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Select::make('data.attributes.type')
                                    ->setLabel(__('global.tmplvars_type'))
                                    ->setUrl('/tvs/types')
                                    ->setData([
                                        [
                                            'key'   => $model->type,
                                            'value' => $model->getStandardTypes()[$model->type] ?? $model->type,
                                        ],
                                    ])
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Input::make('data.attributes.rank')
                                    ->setLabel(__('global.tmplvars_rank'))
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('data.attributes.locked')
                                    ->setLabel(__('global.lock_tmplvars_msg'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Select::make('data.attributes.display')
                                    ->setLabel(__('global.tmplvars_widget'))
                                    ->setUrl('/tvs/display')
                                    ->setData([
                                        [
                                            'key'   => $model->display,
                                            'value' => $model->getDisplay($model->display) ?: __('global.no'),
                                        ],
                                    ])
                                    ->setEmitInput('inputChangeQuery', 'display'),
                            ], ['sm' => '2', 'xl' => '1 / 2 / 1 / 2'])
                            ->when(
                                $model->display,
                                fn(Grid $grid) => $grid
                                    ->addArea($this->display($model->display), ['sm' => '3', 'xl' => '2 / 1 / 2 / 3'])
                            ),
                    ]
                )
                ->addTab(
                    'templates',
                    __('global.templates'),
                    slot: Panel::make('data.templates')
                        ->setId('templates')
                        ->setUrl('/templates?groupBy=category')
                        ->setSlotTop('<p class="p-5">' . __('global.tmplvar_tmpl_access_msg') . '</p>')
                        ->addColumn(
                            'attach',
                            __('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            selectable: true,
                            component: Checkbox::make('templates')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'templatename',
                            __('global.template_name'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.description'),
                            ['width' => '50%'],
                        )
                )
                ->addTab(
                    'roles',
                    __('global.role_management_title'),
                    slot: Panel::make('data.roles')
                        ->setId('roles')
                        ->setUrl('/roles/users')
                        ->setSlotTop('<p class="p-5">' . __('global.tmplvar_roles_access_msg') . '</p>')
                        ->addColumn(
                            'attach',
                            __('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            selectable: true,
                            component: Checkbox::make('roles')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'name',
                            __('global.role'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            __('global.description'),
                            ['width' => '50%'],
                        )
                )
                ->when(
                    auth()->user()->can(['manage_groups', 'manage_tv_permissions']),
                    fn(Tabs $tabs) => $tabs
                        ->addTab(
                            'permissions',
                            __('global.access_permissions'),
                            slot: [
                                __('global.access_permissions_docs_message') . '<br/><br/>',

                                Checkbox::make('data.attributes.is_document_group')
                                    ->setLabel(__('global.all_doc_groups'))
                                    ->setCheckedValue(true, false)
                                    ->setRelation('data.attributes.document_groups', [], [], true)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('data.attributes.document_groups')
                                    ->setLabel(__('global.access_permissions_resource_groups'))
                                    ->setData(
                                        DocumentgroupName::all()
                                            ->map(fn(DocumentgroupName $group) => [
                                                'key'   => $group->getKey(),
                                                'value' => $group->name,
                                            ])
                                            ->toArray()
                                    )
                                    ->setRelation('data.attributes.is_document_group', false, true),
                            ]
                        )
                )
                ->addTab(
                    'settings',
                    __('global.settings_properties'),
                    slot: CodeEditor::make('data.attributes.properties')
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
                ->setAction('sort', __('global.template_tv_edit'), '/tvs/sort', null, 'fa fa-sort')
                ->setNew(
                    __('global.new_tmplvars'),
                    '/tvs/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.tmplvars_management_msg')),

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
                    'tvs',
                    Panel::make('data')
                        ->setId('tvs')
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
                            __('global.tmplvars_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'caption',
                            __('global.tmplvars_caption'),
                            [],
                            true
                        )
                        ->addColumn(
                            'type',
                            __('global.tmplvars_type'),
                            ['width' => '10rem']
                        ),
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin']
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
                    'path'  => '/elements/tvs',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle($this->titleSort())
                ->setIcon($this->iconSort()),

            Panel::make('data')
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
                    __('global.id'),
                    ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold']
                )
                ->addColumn('name', __('global.plugin_name'), ['fontWeight' => 500])
                ->addColumn('caption', __('global.tmplvars_caption'))
                ->addColumn(
                    'rank',
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
            ->setId('tvs')
            ->setTitle($this->titleList())
            ->setIcon($this->iconList())
            ->setPermissions(['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'])
            ->setRoute('/tvs/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('tvs')
                    ->setRoute('/tvs/:id')
                    ->setUrl('/tvs/tree')
                    ->isCategory()
                    ->setAppends(['id'])
                    ->setAliases([
                        'locked' => 'locked:1',
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
                                'title' => __('global.new_tmplvars'),
                                'to'    => [
                                    'path' => '/tvs/0',
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

    /**
     * @param string $name
     *
     * @return array
     */
    public function display(string $name): array
    {
        $name = str($name)->lower()->toString();
        $data = [];

        $widgetParams['date'] =
            '&format=Date Format;string;%A %d, %B %Y &default=If no value, use current date;list;Yes,No;No';
        $widgetParams['string'] = '&format=String Format;list;Upper Case,Lower Case,Sentence Case,Capitalize';
        $widgetParams['delim'] = '&format=Delimiter;string;,';
        $widgetParams['hyperlink'] =
            '&text=Display Text;string; &title=Title;string; &class=Class;string &style=Style;string &target=Target;string &attrib=Attributes;string';
        $widgetParams['htmltag'] =
            '&tagname=Tag Name;string;div &tagid=Tag ID;string &class=Class;string &style=Style;string &attrib=Attributes;string';
        $widgetParams['viewport'] =
            '&vpid=ID/Name;string &width=Width;string;100 &height=Height;string;100 &borsize=Border Size;int;1 &sbar=Scrollbars;list;,Auto,Yes,No &asize=Auto Size;list;,Yes,No &aheight=Auto Height;list;,Yes,No &awidth=Auto Width;list;,Yes,No &stretch=Stretch To Fit;list;,Yes,No &class=Class;string &style=Style;string &attrib=Attributes;string';
        $widgetParams['datagrid'] =
            '&cols=Column Names;string &flds=Field Names;string &cwidth=Column Widths;string &calign=Column Alignments;string &ccolor=Column Colors;string &ctype=Column Types;string &cpad=Cell Padding;int;1 &cspace=Cell Spacing;int;1 &rowid=Row ID Field;string &rgf=Row Group Field;string &rgstyle = Row Group Style;string &rgclass = Row Group Class;string &rowsel=Row Select;string &rhigh=Row Hightlight;string; &psize=Page Size;int;100 &ploc=Pager Location;list;top-right,top-left,bottom-left,bottom-right,both-right,both-left; &pclass=Pager Class;string &pstyle=Pager Style;string &head=Header Text;string &foot=Footer Text;string &tblc=Grid Class;string &tbls=Grid Style;string &itmc=Item Class;string &itms=Item Style;string &aitmc=Alt Item Class;string &aitms=Alt Item Style;string &chdrc=Column Header Class;string &chdrs=Column Header Style;string;&egmsg=Empty message;string;No records found;';
        $widgetParams['richtext'] = '&w=Width;string;100% &h=Height;string;300px &edt=Editor;list;';
        $widgetParams['image'] =
            '&alttext=Alternate Text;string &hspace=H Space;int &vspace=V Space;int &borsize=Border Size;int &align=Align;list;none,baseline,top,middle,bottom,texttop,absmiddle,absbottom,left,right &name=Name;string &class=Class;string &id=ID;string &style=Style;string &attrib=Attributes;string';
        $widgetParams['custom_widget'] = '&output=Output;textarea;[+value+]';

        if (!empty($widgetParams[$name])) {
            $data[] = Panel::make('data')
                ->setColumns([
                    [
                        'name'  => 'title',
                        'label' => __('global.name'),
                    ],
                    [
                        'name'  => 'value',
                        'label' => __('global.value'),
                    ],
                ])
                ->setData($this->parseParams($widgetParams[$name]));
        }

        return $data;
    }

    /**
     * @param string $params
     *
     * @return array
     */
    protected function parseParams(string $params): array
    {
        $params = array_filter(explode('&', $params));
        $data = [];

        foreach ($params as $v) {
            [$key, $values] = explode('=', $v, 2);
            $values = explode(';', trim($values));

            $values[0] ??= '';
            $values[1] ??= '';
            $values[2] ??= '';

            $model = 'display_params_data.' . $key;

            $component = match ($values[1]) {
                'int' => Number::make($model)->setValue($values[2]),
                'list' => Select::make($model)->setData(
                    array_map(
                        fn($i) => [
                            'key'   => $i,
                            'value' => $i,
                        ],
                        explode(',', $values[2])
                    )
                ),
                'textarea' => Textarea::make($model)->setValue($values[2]),
                default => Input::make($model)->setValue($values[2]),
            };

            $data[] = [
                'title' => $values[0],
                'value' => $component,
            ];
        }

        return $data;
    }
}
