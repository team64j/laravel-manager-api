<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\DocumentgroupName;
use EvolutionCMS\Models\SiteTmplvar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Number;
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
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_tmplvars');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.tmplvars');
    }

    /**
     * @return string
     */
    public function titleSort(): string
    {
        return Lang::get('global.template_tv_edit_title');
    }

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
                'title' => $this->titleList() . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/tvs?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/tvs',
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
                            Select::make('display', Lang::get('global.tmplvars_widget'))
                                ->setUrl('/tvs/display')
                                ->setData([
                                    [
                                        'key' => $model->display,
                                        'value' => $model->getDisplay($model->display),
                                    ],
                                ])
                                ->setEmitInput('inputChangeQuery'),
                        ]),
                ])
                ->when(
                    $model->display,
                    fn(Tabs $component) => $component->putSlot(
                        'default',
                        $this->display($model->display)
                    )
                )
                ->addTab('settings', Lang::get('global.settings_properties'))
                ->addSlot('settings', [
                    CodeEditor::make('properties')
                        ->setLanguage('json')
                        ->isFullSize(),
                ])
                ->addTab('templates', Lang::get('global.templates'))
                ->addSlot(
                    'templates',
                    Panel::make()
                        ->setId('templates')
                        ->setModel('templates')
                        ->setUrl('/templates?groupBy=category')
                        ->setSlotTop('<p class="p-5">' . Lang::get('global.tmplvar_tmpl_access_msg') . '</p>')
                        ->addColumn(
                            'attach',
                            Lang::get('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            component: Checkbox::make('templates')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'templatename',
                            Lang::get('global.template_name'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            Lang::get('global.description'),
                            ['width' => '50%'],
                        )
                )
                ->addTab('roles', Lang::get('global.role_management_title'))
                ->addSlot(
                    'roles',
                    Panel::make()
                        ->setId('roles')
                        ->setModel('roles')
                        ->setUrl('/roles/users')
                        ->setSlotTop('<p class="p-5">' . Lang::get('global.tmplvar_roles_access_msg') . '</p>')
                        ->addColumn(
                            'attach',
                            Lang::get('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            component: Checkbox::make('roles')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            Lang::get('global.id'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true
                        )
                        ->addColumn(
                            'name',
                            Lang::get('global.role'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'description',
                            Lang::get('global.description'),
                            ['width' => '50%'],
                        )
                )
                ->when(
                    Auth::user()->can(['manage_groups', 'manage_tv_permissions']),
                    fn(Tabs $tabs) => $tabs
                        ->addTab('permissions', Lang::get('global.access_permissions'))
                        ->addSlot(
                            'permissions',
                            [
                                Lang::get('global.access_permissions_docs_message') . '<br/><br/>',

                                Checkbox::make()
                                    ->setModel('data.is_document_group')
                                    ->setLabel(Lang::get('global.all_doc_groups'))
                                    ->setCheckedValue(true, false)
                                    ->setRelation('data.document_groups', [], [], true),

                                Checkbox::make()
                                    ->setModel('data.document_groups')
                                    ->setLabel(Lang::get('global.access_permissions_resource_groups'))
                                    ->setData(
                                        DocumentgroupName::all()
                                            ->map(fn(DocumentgroupName $group) => [
                                                'key' => $group->getKey(),
                                                'value' => $group->name,
                                            ])
                                            ->toArray()
                                    )
                                    ->setRelation('data.is_document_group', false, true),
                            ]
                        /*Panel::make()
                            ->setId('permissions')
                            ->setModel('permissions')
                            ->setUrl('/permissions/resources')
                            ->setSlotTop('<p class="p-4">' . Lang::get('global.tmplvar_access_msg') . '</p>')
                            ->addColumn(
                                'attach',
                                Lang::get('global.role_udperms'),
                                ['width' => '4rem', 'textAlign' => 'center'],
                                true,
                                component: Checkbox::make('permissions')->setKeyValue('id')
                            )
                            ->addColumn(
                                'id',
                                Lang::get('global.id'),
                                ['width' => '4rem', 'textAlign' => 'center'],
                                true
                            )
                            ->addColumn(
                                'name',
                                Lang::get('global.role'),
                                ['fontWeight' => '500'],
                                true,
                                filter: true
                            )
                            ->addColumn(
                                'description',
                                Lang::get('global.description'),
                                ['width' => '50%'],
                            )*/
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
                ->setAction('sort', Lang::get('global.template_tv_edit'), '/tvs/sort', null, 'fa fa-sort')
                ->setNew(
                    Lang::get('global.new_tmplvars'),
                    '/tvs/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(Lang::get('global.tmplvars_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory(true)
                ->isWatch()
                ->addTab(
                    'templates',
                    Lang::get('global.templates'),
                    'fa fa-newspaper',
                    'py-4',
                    ['edit_template'],
                    route: route('manager.api.elements.templates')
                )
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-list-alt',
                    'py-4',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    route: route('manager.api.elements.tvs')
                )
                ->addTab(
                    'chunks',
                    Lang::get('global.htmlsnippets'),
                    'fa fa-th-large',
                    'py-4',
                    ['edit_chunk'],
                    route: route('manager.api.elements.chunks')
                )
                ->addTab(
                    'snippets',
                    Lang::get('global.snippets'),
                    'fa fa-code',
                    'py-4',
                    ['edit_snippet'],
                    route: route('manager.api.elements.snippets')
                )
                ->addTab(
                    'plugins',
                    Lang::get('global.plugins'),
                    'fa fa-plug',
                    'py-4',
                    ['edit_plugin'],
                    route: route('manager.api.elements.plugins')
                )
                ->addTab(
                    'modules',
                    Lang::get('global.modules'),
                    'fa fa-cubes',
                    'py-4',
                    ['edit_module'],
                    route: route('manager.api.elements.modules')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    'py-4',
                    ['category_manager'],
                    route: route('manager.api.elements.categories')
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
                            true,
                            filter: true
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
    public function sort(): array
    {
        return [
            Actions::make()
                ->setCancelTo([
                    'path' => '/elements/tvs',
                    'close' => true,
                ])
                ->setSave(),

            Title::make()
                ->setTitle($this->titleSort())
                ->setIcon($this->iconSort()),

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
                    ->setAliases([
                        'title' => 'name',
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

    /**
     * @param string $name
     *
     * @return array
     */
    public function display(string $name): array
    {
        $name = Str::lower($name);
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
            $data[] = Panel::make()
                ->setClass('!h-auto !m-0')
                ->setModel('data')
                ->setColumns([
                    [
                        'name' => 'title',
                        'label' => Lang::get('global.name'),
                    ],
                    [
                        'name' => 'value',
                        'label' => Lang::get('global.value'),
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
                            'key' => $i,
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
