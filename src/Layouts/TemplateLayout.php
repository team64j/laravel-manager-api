<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTemplate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
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

class TemplateLayout extends Layout
{
    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_template');
    }

    /**
     * @return string
     */
    public function titleList(): string
    {
        return Lang::get('global.templates');
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
    public function default(SiteTemplate $model = null): array
    {
        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';
        $isBladeFile = file_exists($bladeFile);
        $relativeBladeFile = str_replace([dirname(app_path()), DIRECTORY_SEPARATOR], ['', '/'], $bladeFile);

        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => $this->titleList() . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/templates?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return [
            Actions::make()
                ->setCancel(
                    Lang::get('global.cancel'),
                    [
                        'path' => '/elements/templates',
                        'close' => true,
                    ]
                )
                ->when(
                    $model->getKey(),
                    fn(Actions $component) => $component->setDelete()->setCopy()
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('templatename')
                ->setHelp(Lang::get('global.template_msg'))
                ->setId($model->getKey())
                ->setIcon($this->icon())
                ->setTitle($this->title()),

            Tabs::make()
                ->setId('template')
                ->addTab(
                    'default',
                    Lang::get('global.settings_general'),
                    slot: [
                        Template::make(
                            'flex flex-wrap grow md:basis-2/3 xl:basis-9/12 px-5 pt-5',
                            [
                                Input::make(
                                    'templatename',
                                    Lang::get('global.template_name')
                                )
                                    ->setClass('mb-3')
                                    ->isRequired()
                                    ->setRequired(
                                        Config::get('global.default_template') == $model->getKey() ?
                                            Lang::get('global.defaulttemplate_title') : ''
                                    ),

                                Input::make(
                                    'templatealias',
                                    Lang::get('global.alias')
                                )
                                    ->setClass('mb-3'),

                                Textarea::make(
                                    'description',
                                    Lang::get('global.template_desc')
                                )
                                    ->setClass('mb-3'),
                            ]
                        ),

                        Template::make(
                            'flex flex-wrap grow md:basis-1/3 xl:basis-3/12 px-5 md:!pl-2 md:pt-5',
                            [
                                Select::make(
                                    'category',
                                    Lang::get('global.existing_category')
                                )
                                    ->setClass('mb-3')
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
                                    ->setClass('mb-3')
                                    ->setCheckedValue(1, 0),

                                Checkbox::make('locked', Lang::get('global.lock_template_msg'))
                                    ->setClass('mb-3')
                                    ->setCheckedValue(1, 0),
                            ]
                        ),

                        ($isBladeFile
                            ? '<span class="text-green-600 mx-5 mb-3">' .
                            Lang::get('global.template_assigned_blade_file') .
                            ': ' .
                            $relativeBladeFile . '</span>'
                            :
                            Checkbox::make('createbladefile', Lang::get('global.template_create_blade_file'))
                                ->setClass('mx-5 mb-3')
                                ->setCheckedValue(1, 0)),

                        CodeEditor::make('content', Lang::get('global.template_code'))
                            ->setClass('px-5')
                            ->setLanguage('html')
                            ->setRows(20),
                    ]
                )
                ->addTab(
                    'tvs',
                    Lang::get('global.template_assignedtv_tab'),
                    slot: Panel::make()
                        ->setId('tvs')
                        ->setUrl('/templates/' . ($model->getKey() ?: 'new') . '/tvs?attach=true')
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
                            true,
                            filter: true
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
                ->addTab(
                    'available',
                    Lang::get('global.template_notassigned_tv'),
                    slot: Panel::make()
                        ->setId('available')
                        ->setModel('tvs')
                        ->setUrl('/templates/' . ($model->getKey() ?: 'new') . '/tvs?attach=false')
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
                            true,
                            filter: true
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
                ->addTab(
                    'settings',
                    Lang::get('global.settings_properties'),
                    slot: CodeEditor::make('properties')
                        ->setClass('p-5')
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
                ->setNew(
                    $this->title(),
                    '/templates/new',
                    'btn-green',
                    'fa fa-plus'
                ),

            Title::make()
                ->setTitle($this->titleList())
                ->setIcon($this->iconList())
                ->setHelp(Lang::get('global.template_management_msg')),

            Tabs::make()
                ->setId('elements')
                ->setHistory(true)
                ->isWatch()
                ->addTab(
                    'templates',
                    Lang::get('global.templates'),
                    'fa fa-newspaper',
                    '',
                    ['edit_template'],
                    route('manager.api.elements.templates'),
                )
                ->addTab(
                    'tvs',
                    Lang::get('global.tmplvars'),
                    'fa fa-list-alt',
                    '',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    route('manager.api.elements.tvs')
                )
                ->addTab(
                    'chunks',
                    Lang::get('global.htmlsnippets'),
                    'fa fa-th-large',
                    '',
                    ['edit_chunk'],
                    route('manager.api.elements.chunks')
                )
                ->addTab(
                    'snippets',
                    Lang::get('global.snippets'),
                    'fa fa-code',
                    '',
                    ['edit_snippet'],
                    route('manager.api.elements.snippets')
                )
                ->addTab(
                    'plugins',
                    Lang::get('global.plugins'),
                    'fa fa-plug',
                    '',
                    ['edit_plugin'],
                    route('manager.api.elements.plugins')
                )
                ->addTab(
                    'modules',
                    Lang::get('global.modules'),
                    'fa fa-cubes',
                    '',
                    ['edit_module'],
                    route('manager.api.elements.modules')
                )
                ->addTab(
                    'categories',
                    Lang::get('global.category_management'),
                    'fa fa-object-group',
                    '',
                    ['category_manager'],
                    route('manager.api.elements.categories')
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
                            true,
                            filter: true
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
                    ->setAliases([
                        'title' => ['name', 'templatename'],
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => $this->icon(),
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
