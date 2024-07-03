<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteHtmlSnippet;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\ActionsButtons;
use Team64j\LaravelManagerComponents\Breadcrumbs;
use Team64j\LaravelManagerComponents\Main;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class ChunkLayout extends Layout
{
    /**
     * @param SiteHtmlSnippet|null $model
     *
     * @return array
     */
    public function default(SiteHtmlSnippet $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.htmlsnippets') . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/chunks?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancel(
                        Lang::get('global.cancel'),
                        [
                            'path' => '/elements/chunks',
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
                    ->setTitle(Lang::get('global.new_htmlsnippet'))
                    ->setIcon('fa fa-th-large')
                    ->setId($model->getKey())
            )
            ->setTabs(
                fn(Tabs $component) => $component
            )
            ->setBreadcrumbs(
                fn(Breadcrumbs $component) => $component->setData($breadcrumbs)
            )
            ->toArray();
    }

    public function list(): array
    {
        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setNew(
                        Lang::get('global.new_htmlsnippet'),
                        '/chunks/new',
                        'btn-green',
                        'fa fa-plus'
                    )
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setTitle(Lang::get('global.htmlsnippets'))
                    ->setIcon('fa fa-th-large')
                    ->setHelp(Lang::get('global.htmlsnippet_management_msg'))
            )
            ->setTabs(
                fn(Tabs $component) => $component
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
                        'fa fa-th-large',
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
                        'chunks',
                        Panel::make()
                            ->setId('chunks')
                            ->setModel('data')
                            ->setRoute('/chunks/:id')
                            ->setHistory(true)
                            ->addColumn(
                                ['#', 'locked'],
                                null,
                                ['width' => '3rem'],
                                false,
                                [
                                    '<i class="fa fa-th-large fa-fw"/>',
                                    '<i class="fa fa-th-large fa-fw" title="' .
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
                                Lang::get('global.htmlsnippet_name'),
                                ['width' => '20rem', 'fontWeight' => 500],
                                true,
                                filter: true
                            )
                            ->addColumn(
                                'description',
                                Lang::get('global.htmlsnippet_desc')
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
                                'disabled',
                                Lang::get('global.disabled'),
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
                        ['edit_chunk']
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
            'title' => Lang::get('global.htmlsnippets'),
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-th-large';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('chunks')
            ->setTitle(Lang::get('global.htmlsnippets'))
            ->setIcon('fa fa-th-large')
            ->setPermissions('edit_chunk')
            ->setRoute('/chunks/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('chunks')
                    ->setRoute('/chunks/:id')
                    ->setUrl('/chunks/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
                        'deleted' => 'disabled',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-th-large',
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
