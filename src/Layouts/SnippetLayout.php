<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteSnippet;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\ActionsButtons;
use Team64j\LaravelManagerComponents\Breadcrumbs;
use Team64j\LaravelManagerComponents\Main;
use Team64j\LaravelManagerComponents\Panel;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class SnippetLayout extends Layout
{
    /**
     * @param SiteSnippet|null $model
     *
     * @return array
     */
    public function default(SiteSnippet $model = null): array
    {
        $category = $model->category()->firstOr(fn() => new Category());

        $breadcrumbs = [
            [
                'id' => $category->getKey() ?? 0,
                'title' => Lang::get('global.snippets') . ': ' . ($category->category ?? Lang::get('global.no_category')),
                'to' => '/elements/snippets?groupBy=none&category=' . ($category->getKey() ?? 0),
            ],
        ];

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancel(
                        Lang::get('global.cancel'),
                        [
                            'path' => '/elements/snippets',
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
                    ->setTitle(Lang::get('global.new_snippet'))
                    ->setIcon('fa fa-code')
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
                        Lang::get('global.new_snippet'),
                        '/snippets/new',
                        'btn-green',
                        'fa fa-plus'
                    )
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setTitle(Lang::get('global.snippets'))
                    ->setIcon('fa fa-code')
                    ->setHelp(Lang::get('global.snippet_management_msg'))
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
                        'snippets',
                        Panel::make()
                            ->setId('snippets')
                            ->setModel('data')
                            ->setRoute('/snippets/:id')
                            ->setHistory(true)
                            ->addColumn(
                                ['#', 'locked'],
                                null,
                                ['width' => '3rem'],
                                false,
                                [
                                    '<i class="fa fa-code fa-fw"/>',
                                    '<i class="fa fa-code fa-fw" title="' .
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
                                Lang::get('global.snippet_name'),
                                ['width' => '20rem', 'fontWeight' => 500],
                                true,
                                filter: true
                            )
                            ->addColumn(
                                'description',
                                Lang::get('global.snippet_desc')
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
                        ['edit_snippet']
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
            'title' => Lang::get('global.snippets'),
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-code';
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('snippets')
            ->setTitle(Lang::get('global.snippets'))
            ->setIcon('fa fa-code')
            ->setPermissions('edit_snippet')
            ->setRoute('/snippets/:id')
            ->isNeedUpdate()
            ->setSlot(
                Tree::make()
                    ->setId('snippets')
                    ->setRoute('/snippets/:id')
                    ->setUrl('/snippets/tree')
                    ->isCategory()
                    ->setAliases([
                        'title' => 'name',
                        'deleted' => 'disabled',
                    ])
                    ->setAppends(['id'])
                    ->setIcons([
                        'default' => 'fa fa-code',
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
