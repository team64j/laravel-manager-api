<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteTemplate;
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

class TemplateLayout extends Layout
{
    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_template');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return __('global.templates');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-newspaper';
    }

    /**
     * @return string
     */
    public function iconList(): string
    {
        return 'fa fa-newspaper';
    }

    /**
     * @param SiteTemplate|null $model
     *
     * @return array
     */
    public function default(?SiteTemplate $model = null): array
    {
        $bladeFile = current(config('view.paths')) . '/' . $model->templatealias . '.blade.php';
        $isBladeFile = file_exists($bladeFile);
        $relativeBladeFile = str_replace([dirname(app_path()), DIRECTORY_SEPARATOR], ['', '/'], $bladeFile);

        /** @var Category $category */
        $category = $model->category()->firstOr(
            fn() => new Category()->setAttribute('id', 0)->setAttribute('category', __('global.no_category'))
        );

        $breadcrumbs = [
            [
                'id'    => $category->getKey(),
                'title' => $this->titleList() . ': ' . $category->category,
                'to'    => '/elements/templates?groupBy=none&category=' . $category->getKey(),
            ],
        ];

        return [
            GlobalTab::make()
                ->setIcon($this->icon())
                ->setTitle($this->title($model->templatename)),

            Actions::make()
                ->setCancel(
                    __('global.cancel'),
                    [
                        'path'  => '/elements/templates',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $component) => $component->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make('data.attributes.templatename')
                ->setHelp(__('global.template_msg'))
                ->setId($model->getKey())
                ->setIcon($this->icon())
                ->setTitle($this->title()),

            Tabs::make()
                ->setId('template')
                ->addTab(
                    'default',
                    __('global.settings_general'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25rem')
                            ->addArea([
                                Input::make('data.attributes.templatename')
                                    ->setLabel(__('global.template_name'))
                                    ->isRequired()
                                    ->setRequired(
                                        config('global.default_template') == $model->getKey() ?
                                            __('global.defaulttemplate_title') : ''
                                    )
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Input::make('data.attributes.templatealias')
                                    ->setLabel(__('global.alias'))
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Textarea::make('data.attributes.description')
                                    ->setLabel(__('global.template_desc')),
                            ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 3'])
                            ->addArea([
                                Select::make('data.attributes.category')
                                    ->setLabel(__('global.existing_category'))
                                    ->setUrl('/categories/select')
                                    ->addOption(
                                        $category->getKey(),
                                        $category->category
                                    )
                                    ->setNew('')
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('data.attributes.selectable')
                                    ->setLabel(__('global.template_selectable'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Checkbox::make('data.attributes.locked')
                                    ->setLabel(__('global.lock_template_msg'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),
                            ], ['sm' => '2', 'xl' => '1 / 3 / 1 / 3'])
                            ->addArea([
                                ($isBladeFile
                                    ? '<p class="text-success">' .
                                    __('global.template_assigned_blade_file') .
                                    ': ' .
                                    $relativeBladeFile . '</p>'
                                    :
                                    Checkbox::make('data.attributes.createbladefile')
                                        ->setLabel(__('global.template_create_blade_file'))
                                        ->setCheckedValue(1, 0)),

                                CodeEditor::make('data.attributes.content')
                                    ->setLabel(__('global.template_code'))
                                    ->setLanguage('html')
                                    ->setRows(20),
                            ], ['sm' => '3', 'xl' => '2 / 1 / 2 / 4']),
                    ]
                )
                ->addTab(
                    'tvs',
                    __('global.template_assignedtv_tab'),
                    slot: Panel::make('tvs')
                        ->setId('tvs')
                        ->setUrl('/templates/' . intval($model->getKey()) . '/tvs?attach=true')
                        ->addColumn(
                            'attach',
                            __('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            selectable: true,
                            component: Checkbox::make('tvs')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            'ID',
                            ['width' => '4rem', 'textAlign' => 'right'],
                            true
                        )
                        ->addColumn(
                            'name',
                            __('global.tmplvars_name'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'caption',
                            __('global.tmplvars_caption'),
                            ['width' => '50%'],
                        )
                        ->addColumn(
                            'rank',
                            __('global.tmplvars_rank'),
                            ['width' => '12rem', 'textAlign' => 'center']
                        )
                )
                ->addTab(
                    'available',
                    __('global.template_notassigned_tv'),
                    slot: Panel::make('tvs')
                        ->setId('available')
                        ->setUrl('/templates/' . intval($model->getKey()) . '/tvs?attach=false')
                        ->addColumn(
                            'attach',
                            __('global.role_udperms'),
                            ['width' => '4rem', 'textAlign' => 'center'],
                            true,
                            selectable: true,
                            component: Checkbox::make('tvs')->setKeyValue('id')
                        )
                        ->addColumn(
                            'id',
                            'ID',
                            ['width' => '4rem', 'textAlign' => 'right'],
                            true
                        )
                        ->addColumn(
                            'name',
                            __('global.tmplvars_name'),
                            ['fontWeight' => '500'],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'caption',
                            __('global.tmplvars_caption'),
                            ['width' => '50%'],
                        )
                        ->addColumn(
                            'rank',
                            __('global.tmplvars_rank'),
                            ['width' => '12rem', 'textAlign' => 'center']
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
            GlobalTab::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList()),

            Actions::make()
                ->setNew(
                    $this->title(),
                    '/templates/0',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(__('global.template_management_msg')),

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
                    'templates',
                    Panel::make('data')
                        ->setId('templates')
                        ->setRoute('/templates/:id')
                        ->setHistory(true)
                        ->addColumn(
                            ['#', 'locked'],
                            null,
                            ['width' => '3rem'],
                            false,
                            [
                                'id'     => [
                                    config(
                                        'global.default_template'
                                    ) => '<i class="fa fa-home fa-fw text-blue-500" data-tooltip="' .
                                        __('global.defaulttemplate_title') . '"></i>',
                                ],
                                'locked' => [
                                    '<i class="fa fa-newspaper fa-fw"></i>',
                                    '<i class="fa fa-newspaper fa-fw" data-tooltip="' . __('global.locked') .
                                    '"><i class="fa fa-lock"></i></i>',
                                ],
                            ]
                        )
                        ->addColumn(
                            'id',
                            __('global.id'),
                            ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                            true
                        )
                        ->addColumn(
                            'templatename',
                            __('global.template_name'),
                            ['width' => '20rem', 'fontWeight' => 500],
                            true,
                            filter: true
                        )
                        ->addColumn(
                            'file',
                            __('global.files_management'),
                            ['width' => '5rem', 'textAlign' => 'center']
                        )
                        ->addColumn(
                            'description',
                            __('global.template_desc')
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
            ->setId('templates')
            ->setTitle($this->titleList())
            ->setIcon($this->iconList())
            ->setPermissions('edit_template')
            ->setRoute('/templates/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('templates')
                    ->setRoute('/templates/:id')
                    ->setUrl('/templates/tree')
                    ->isCategory()
                    ->setAppends(['id'])
                    ->setAliases([
                        'locked' => 'locked:1',
                    ])
                    ->setIcons([
                        'default'                         => $this->icon(),
                        config('global.default_template') => 'fa fa-home fa-fw text-blue-500',
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
                                'title' => __('global.new_template'),
                                'to'    => [
                                    'path' => '/templates/0',
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
