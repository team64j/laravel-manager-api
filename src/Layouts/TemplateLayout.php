<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTemplate;
use Illuminate\Support\Facades\Config;
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

class TemplateLayout extends Layout
{
    /**
     * @param SiteTemplate|null $model
     *
     * @return array
     */
    public function default(SiteTemplate $model = null): array
    {
        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';
        $isBladeFile = file_exists($bladeFile);
        $relativeBladeFile = str_replace([dirname(app_path()), DIRECTORY_SEPARATOR], ['', '/'], $bladeFile);

        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.templates') . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/templates?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancel(
                        Lang::get('global.cancel'),
                        [
                            'path' => '/elements/templates',
                            'close' => true,
                        ]
                    )
                    ->when(
                        $model->getKey(),
                        fn(ActionsButtons $component) => $component->setDelete()->setCopy()
                    )
                    ->setSaveAnd()
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setModel('templatename')
                    ->setHelp(Lang::get('global.template_msg'))
                    ->setId($model->getKey())
                    ->setIcon('fa fa-newspaper')
                    ->setTitle(Lang::get('global.new_template'))
            )
            ->setTabs(
                fn(Tabs $component) => $component
                    ->setId('template')
                    ->addTab('default', Lang::get('global.settings_general'))
                    ->addSlot(
                        'default',
                        [
                            Template::make(
                                'flex flex-wrap grow md:basis-2/3 xl:basis-9/12 md:pr-3',
                                [
                                    Input::make(
                                        'templatename',
                                        Lang::get('global.template_name')
                                    )
                                        ->isRequired()
                                        ->setRequired(
                                            Config::get('global.default_template') == $model->id ? Lang::get(
                                                'global.defaulttemplate_title'
                                            ) : ''
                                        ),

                                    Input::make(
                                        'templatealias',
                                        Lang::get('global.alias')
                                    ),

                                    Textarea::make(
                                        'description',
                                        Lang::get('global.template_desc')
                                    ),
                                ]
                            ),

                            Template::make(
                                'flex flex-wrap grow md:basis-1/3 xl:basis-3/12 md:pl-3',
                                [
                                    Select::make(
                                        'category',
                                        Lang::get('global.existing_category')
                                    )
                                        ->setUrl('/categories/select')
                                        ->addOption(
                                            $model->category,
                                            $model->categories
                                                ? $model->categories->category
                                                : Lang::get(
                                                'global.no_category'
                                            )
                                        )
                                        ->setNew(''),

                                    Checkbox::make('selectable', Lang::get('global.template_selectable'))
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make('locked', Lang::get('global.lock_template_msg'))
                                        ->setCheckedValue(1, 0),
                                ]
                            ),

                            ($isBladeFile
                                ? '<span class="text-green-600 mb-3">' .
                                Lang::get('global.template_assigned_blade_file') .
                                ': ' .
                                $relativeBladeFile . '</span>'
                                :
                                Checkbox::make('createbladefile', Lang::get('global.template_create_blade_file'))
                                    ->setCheckedValue(1, 0)),

                            CodeEditor::make('content', Lang::get('global.template_code'))
                                ->setLanguage('html')
                                ->setRows(20),
                        ]
                    )
                    ->addTab('tvs', Lang::get('global.template_assignedtv_tab'))
                    ->addSlot(
                        'tvs',
                        Panel::make()
                            ->setId('tvs')
                            ->setHistory(true)
                            ->isFilter()
                            ->setSlotTop('<div class="font-bold">' . Lang::get('global.template_tv_msg') . '</div>')
                            ->setUrl('/templates/' . ($model->getKey() ?: 'new') . '/tvs')
                            ->setModel('tvs')
                            ->addColumn(
                                'attach',
                                Lang::get('global.role_udperms'),
                                ['width' => '4rem', 'textAlign' => 'center'],
                                true
                            )
                            ->addColumn(
                                'id',
                                'ID',
                                ['width' => '4rem', 'textAlign' => 'right'],
                                true
                            )
                            ->addColumn(
                                'name',
                                Lang::get('global.tmplvars_name'),
                                ['fontWeight' => '500'],
                                true
                            )
                            ->addColumn(
                                'caption',
                                Lang::get('global.tmplvars_caption'),
                                ['width' => '50%'],
                            )
                            ->addColumn(
                                'rank',
                                Lang::get('global.tmplvars_rank'),
                                ['width' => '12rem', 'textAlign' => 'center']
                            )
                    )
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
                fn(ActionsButtons $component) => $component->setNew(
                    Lang::get('global.new_template'),
                    '/templates/new',
                    'btn-green',
                    'fa fa-plus'
                )
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setTitle(Lang::get('global.templates'))
                    ->setIcon('fa fa-newspaper')
                    ->setHelp(Lang::get('global.template_management_msg'))
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
                        'templates',
                        Panel::make()
                            ->setId('templates')
                            ->setModel('data')
                            ->setRoute('/templates/:id')
                            ->setHistory(true)
                            ->addColumn(
                                '#',
                                null,
                                ['width' => '3rem'],
                                false,
                                [
                                    'id' => [
                                        Config::get(
                                            'global.default_template'
                                        ) => '<i class="fa fa-home fa-fw text-blue-500" data-tooltip="' .
                                            Lang::get('global.defaulttemplate_title') . '"></i>',
                                    ],
                                    'locked' => [
                                        '<i class="fa fa-newspaper fa-fw"></i>',
                                        '<i class="fa fa-newspaper fa-fw" data-tooltip="' . Lang::get('global.locked') .
                                        '"><i class="fa fa-lock"></i></i>',
                                    ],
                                ]
                            )
                            ->addColumn(
                                'id',
                                Lang::get('global.id'),
                                ['width' => '5rem', 'textAlign' => 'right', 'fontWeight' => 'bold'],
                                true
                            )
                            ->addColumn(
                                'templatename',
                                Lang::get('global.template_name'),
                                ['width' => '20rem', 'fontWeight' => 500],
                                true
                            )
                            ->addColumn(
                                'file',
                                Lang::get('global.files_management'),
                                ['width' => '5rem', 'textAlign' => 'center']
                            )
                            ->addColumn(
                                'description',
                                Lang::get('global.template_desc')
                            )
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
                        ['edit_template']
                    )
            )
            ->toArray();
    }

    /**
     * @return array
     */
    public function titleList(): array
    {
        return [
            'title' => Lang::get('global.templates'),
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-newspaper';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('templates')
            ->setTitle(Lang::get('global.templates'))
            ->setIcon('fa fa-newspaper')
            ->setPermissions('edit_template')
            ->setRoute('/templates/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('templates')
                    ->setRoute('/templates/:id')
                    ->setUrl('/templates/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => ['name', 'templatename'],
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-newspaper',
                        Config::get('global.default_template') => 'fa fa-home fa-fw text-blue-500',
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
