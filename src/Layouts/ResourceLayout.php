<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Team64j\LaravelManagerApi\Models\DocumentgroupName;
use Team64j\LaravelManagerApi\Models\SiteContent;
use Team64j\LaravelManagerApi\Models\SiteTmplvar;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\DateTime;
use Team64j\LaravelManagerComponents\Email;
use Team64j\LaravelManagerComponents\Field;
use Team64j\LaravelManagerComponents\File;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Grid;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Number;
use Team64j\LaravelManagerComponents\Radio;
use Team64j\LaravelManagerComponents\Section;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class ResourceLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-edit';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.new_resource');
    }

    /**
     * @param $items
     * @param array $parents
     *
     * @return array
     */
    protected function getParents($items, array &$parents = []): array
    {
        if (!$parents) {
            $parents[] = [
                'id'     => 0,
                'parent' => null,
                'title'  => 'root',
                'to'     => [
                    'path' => '/resources/0',
                ],
            ];
        }

        return array_map(
            function ($item) use (&$parents) {
                if (!empty($item['parents'])) {
                    $this->getParents([$item['parents']], $parents);
                }

                $parents[] = [
                    'id'     => $item['id'],
                    'parent' => $item['parent'],
                    'title'  => $item['pagetitle'],
                    'to'     => [
                        'path' => '/resource/' . $item['id'],
                    ],
                ];
            },
            $items
        );
    }

    /**
     * @param SiteContent|null $model
     *
     * @return array
     */
    public function default(?SiteContent $model = null): array
    {
        /**
         * @param $items
         *
         * @return Generator
         */
        function flatten($items): Generator
        {
            foreach ($items as $item) {
                yield $item;
                if (!empty($item->parents)) {
                    foreach (flatten([$item->parents]) as $child) {
                        yield $child;
                    }
                }
            }
        }

        $breadcrumbs = array_reverse(
            array_merge(
                array_map(
                    fn(SiteContent $item) => [
                        'id'      => $item->getKey(),
                        'parent'  => $item->parent,
                        'name'    => $item->pagetitle,
                        'to'      => '/resource/' . $item->getKey(),
                        'tooltip' => 'ID: ' . $item->getKey() . '<br>' . $item->pagetitle,
                    ],
                    $model->parents ? iterator_to_array(flatten([$model->parents])) : []
                ),
                [
                    [
                        'id'      => 0,
                        'parent'  => null,
                        'name'    => 'root',
                        'to'      => '/resources/0',
                        'tooltip' => 'ID: 0<br>root',
                    ],
                ]
            )
        );

        if (request()->input('type') == 'reference') {
            $filedContent = Input::make('data.attributes.content')
                ->setLabel(__('global.weblink'))
                ->setHelp('<b>[*content*]</b><br>' . __('global.resource_weblink_help'));

            $title = __('global.untitled_weblink');
        } else {
            $filedContent = CodeEditor::make('data.attributes.content')
                ->setLabel(__('global.resource_content'))
                ->setHelp('<b>[*content*]</b>')
                ->setRows(20)
                ->setLanguage('html');

            $title = $this->title();
        }

        $tvs = $model->tvs;
        $tabTvs = $this->tabTvs($tvs);
        $groupTv = $tvs->count() ? config('global.group_tvs') : '';
        $route = URL::getRouteById($model->getKey());

        return [
            GlobalTab::make()
                ->setIcon($this->icon())
                ->setTitle($this->title($model->pagetitle)),

            Actions::make()
                ->setCancelTo([
                    'path'  => '/resources/' . $model->parent,
                    'close' => true,
                ])
                ->when(
                    $model->exists,
                    fn(Actions $actions): Actions => $actions->setViewTo(['href' => $route['url'] ?? ''])
                )
                ->when(
                    $model->deleted,
                    fn(Actions $component) => $component
                        ->setAction(
                            [
                                'action' => 'update',
                                'method' => 'delete',
                            ],
                            __('global.undelete_resource'),
                            null,
                            'btn-blue',
                            'fa fa-undo'
                        ),
                    fn(Actions $component) => $component->when(
                        $model->getKey(),
                        fn(Actions $component) => $component
                            ->setAction(
                                [
                                    'action' => 'update',
                                    'method' => 'delete',
                                ],
                                __('global.delete'),
                                null,
                                'btn-red',
                                'fa fa-trash-alt'
                            )
                            ->setCopy(to: ['path' => '/resource/0?id=' . $model->getKey()]),
                    )
                )
                ->setSaveAnd(),

            Title::make('data.attributes.pagetitle')
                ->setTitle($title)
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('resource')
                ->addTab(
                    'general',
                    __('global.settings_general'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25em')
                            ->addArea([
                                Input::make('data.attributes.pagetitle')
                                    ->setLabel(__('global.resource_title'))
                                    ->setHelp('<b>[*pagetitle*]</b><br>' . __('global.resource_title_help'))
                                    ->isRequired()
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Input::make('data.attributes.alias')
                                    ->setLabel(__('global.resource_alias'))
                                    ->setHelp('<b>[*alias*]</b><br>' . __('global.resource_alias_help'))
                                    ->isRequired()
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Input::make('data.attributes.longtitle')
                                    ->setLabel(__('global.long_title'))
                                    ->setHelp('<b>[*longtitle*]</b><br>' . __('global.resource_long_title_help'))
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Textarea::make('data.attributes.description')
                                    ->setLabel(__('global.resource_description'))
                                    ->setHelp('<b>[*description*]</b><br>' . __('global.resource_description_help'))
                                    ->setRows(3)
                                    ->setAttribute('style', ['margin-bottom' => '1rem', 'height' => '10rem']),

                                CodeEditor::make('data.attributes.introtext')
                                    ->setLabel(__('global.resource_summary'))
                                    ->setHelp('<b>[*introtext*]</b><br>' . __('global.resource_summary_help'))
                                    ->setRows(3)
                                    ->setLanguage('html')
                                    ->setAttribute('style', ['height' => '10rem']),
                            ], ['sm' => '1', 'xl' => '1 / 1 / 1 / 8'])
                            ->addArea([
                                /*Select::make('parent')
                                    ->setLabel(__('global.import_parent_resource'))
                                    ->setHelp('<b>[*parent*]</b><br>' . __('global.resource_parent_help'))
                                    ->setUrl('/resource/select')
                                    ->setData([
                                        [
                                            'key' => $model->parent,
                                            'value' => $model->parent ? $model->parent . ' - ' .
                                                $model->parents->pagetitle : '0 - root',
                                            'selected' => true,
                                        ],
                                    ])
                                    ->setEmitInput('inputChangeQuery'),*/

                                Input::make('data.attributes.parent')
                                    ->setLabel(__('global.import_parent_resource'))
                                    ->setHelp('<b>[*parent*]</b><br>' . __('global.resource_parent_help'))
                                    ->setInputClass('cursor-pointer')
                                    ->setValue(
                                        $model->parent ? $model->parent . ' - ' . $model->parents->pagetitle
                                            : '0 - root'
                                    )
                                    ->setEmitClick('inputTreeSelect')
                                    ->isRequired()
                                    ->isReadonly()
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Select::make('data.attributes.template')
                                    ->setLabel(__('global.page_data_template'))
                                    ->setHelp('<b>[*template*]</b><br>' . __('global.page_data_template_help'))
                                    ->setUrl('/templates/select')
                                    ->setData([
                                        [
                                            'key'      => $model->template ?? 0,
                                            'value'    => ($model->tpl->templatename ?? 'blank') . ' (' .
                                                ($model->template ?? 0) . ')',
                                            'selected' => true,
                                        ],
                                    ])
                                    ->setEmitInput('inputChangeQuery', 'template')
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.hidemenu')
                                    ->setLabel(__('global.resource_opt_show_menu'))
                                    ->setHelp('<b>[*hidemenu*]</b><br>' . __('global.resource_opt_show_menu_help'))
                                    ->setCheckedValue(0, 1)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Number::make('data.attributes.menuindex')
                                    ->setLabel(__('global.resource_opt_menu_index'))
                                    ->setHelp('<b>[*menuindex*]</b><br>' . __('global.resource_opt_menu_index_help'))
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Input::make('data.attributes.menutitle')
                                    ->setLabel(__('global.resource_opt_menu_title'))
                                    ->setHelp('<b>[*menutitle*]</b><br>' . __('global.resource_opt_menu_title_help'))
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Input::make('data.attributes.link_attributes')
                                    ->setLabel(__('global.link_attributes'))
                                    ->setHelp('<b>[*link_attributes*]</b><br>' . __('global.link_attributes_help'))
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.published')
                                    ->setLabel(__('global.resource_opt_published'))
                                    ->setHelp('<b>[*published*]</b><br>' . __('global.resource_opt_published_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                DateTime::make('data.attributes.publishedon')
                                    ->setLabel(__('global.page_data_published'))
                                    ->isClear()
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                DateTime::make('data.attributes.pub_date')
                                    ->setLabel(__('global.page_data_publishdate'))
                                    ->setHelp('<b>[*pub_date*]</b><br>' . __('global.page_data_publishdate_help'))
                                    ->isClear()
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                DateTime::make('data.attributes.unpub_date')
                                    ->setLabel(__('global.page_data_unpublishdate'))
                                    ->setHelp('<b>[*unpub_date*]</b><br>' . __('global.page_data_unpublishdate_help'))
                                    ->isClear(),
                            ], ['sm' => '3', 'xl' => '1 / 8 / 1 / 8'])
                            ->addArea([
                                $filedContent,
                            ], ['sm' => '2', 'xl' => '2 / 1 / 2 / 9'])
                            ->when(
                                $groupTv == 0,
                                fn(Grid $grid) => $grid->addArea(
                                    Arr::flatten($tabTvs['slots']),
                                    ['sm' => '4', 'xl' => '3 / 1 / 3 / 9']
                                )
                            )
                            ->when(
                                $groupTv == 1,
                                fn(Grid $grid) => $grid->addArea(
                                    array_map(
                                        fn($slot) => Section::make()
                                            ->setLabel($slot['name'])
                                            ->setSlot($tabTvs['slots'][$slot['id']])
                                            ->isExpanded(),
                                        $tabTvs['attrs']['data']
                                    ),
                                    ['sm' => '4', 'xl' => '3 / 1 / 3 / 9']
                                )
                            )
                            ->when(
                                $groupTv == 2,
                                fn(Grid $grid) => $grid->addArea(
                                    $tabTvs,
                                    ['sm' => '4', 'xl' => '3 / 1 / 3 / 9']
                                )
                            ),
                    ]
                )
                ->addTab(
                    'settings',
                    __('global.settings_page_settings'),
                    slot: [
                        Grid::make()
                            ->setGap('1.25rem')
                            ->addArea([
                                Select::make('data.attributes.type')
                                    ->setLabel(__('global.resource_type'))
                                    ->setHelp('<b>[*type*]</b><br>' . __('global.resource_type_message'))
                                    ->setData([
                                        [
                                            'key'   => 'document',
                                            'value' => __('global.resource_type_webpage'),
                                        ],
                                        [
                                            'key'   => 'reference',
                                            'value' => __('global.resource_type_weblink'),
                                        ],
                                    ])
                                    ->setEmitInput('inputChangeQuery', 'type')
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Select::make('data.attributes.contentType')
                                    ->setLabel(__('global.page_data_contentType'))
                                    ->setHelp('<b>[*contentType*]</b><br>' . __('global.page_data_contentType_help'))
                                    ->setData(
                                        array_map(fn($k) => [
                                            'key'   => $k,
                                            'value' => $k,
                                        ], explode(',', config('global.custom_contenttype', 'text/html')))
                                    )
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),

                                Select::make('data.attributes.content_dispo')
                                    ->setLabel(__('global.resource_opt_contentdispo'))
                                    ->setHelp(
                                        '<b>[*content_dispo*]</b><br>' . __('global.resource_opt_contentdispo_help')
                                    )
                                    ->setData([
                                        [
                                            'key'   => 0,
                                            'value' => __('global.inline'),
                                        ],
                                        [
                                            'key'   => 1,
                                            'value' => __('global.attachment'),
                                        ],
                                    ])
                                    ->setAttribute('style', ['margin-bottom' => '1rem']),
                            ], ['sm' => '1', 'xl' => '1 / 1'])
                            ->addArea([
                                Checkbox::make('data.attributes.isfolder')
                                    ->setLabel(__('global.resource_opt_folder'))
                                    ->setHelp('<b>[*isfolder*]</b><br>' . __('global.resource_opt_folder_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.hide_from_tree')
                                    ->setLabel(__('global.track_visitors_title'))
                                    ->setHelp(
                                        '<b>[*hide_from_tree*]</b><br>' .
                                        __('global.resource_opt_trackvisit_help')
                                    )
                                    ->setCheckedValue(0, 1)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.alias_visible')
                                    ->setLabel(__('global.resource_opt_alvisibled'))
                                    ->setHelp(
                                        '<b>[*alias_visible*]</b><br>' .
                                        __('global.resource_opt_alvisibled_help')
                                    )
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.richtext')
                                    ->setLabel(__('global.resource_opt_richtext'))
                                    ->setHelp('<b>[*richtext*]</b><br>' . __('global.resource_opt_richtext_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.searchable')
                                    ->setLabel(__('global.page_data_searchable'))
                                    ->setHelp('<b>[*searchable*]</b><br>' . __('global.page_data_searchable_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.cacheable')
                                    ->setLabel(__('global.page_data_cacheable'))
                                    ->setHelp('<b>[*cacheable*]</b><br>' . __('global.page_data_cacheable_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setAttribute('style', ['margin-bottom' => '0.5rem']),

                                Checkbox::make('data.attributes.empty_cache')
                                    ->setLabel(__('global.resource_opt_emptycache'))
                                    ->setHelp(__('global.resource_opt_emptycache_help'))
                                    ->setCheckedValue(1, 0)
                                    ->setValue(1),
                            ], ['sm' => '2', 'xl' => '1 / 2']),
                    ]
                )
                ->when(
                    $groupTv == 3,
                    fn(Tabs $tabs) => $tabs->addTab(
                        'tvs',
                        __('global.settings_templvars'),
                        slot: array_map(
                            fn($slot) => Section::make()
                                ->setLabel($slot['name'])
                                ->setSlot($tabTvs['slots'][$slot['id']])
                                ->isExpanded(),
                            $tabTvs['attrs']['data']
                        )
                    )
                )
                ->when(
                    $groupTv == 4,
                    fn(Tabs $tabs) => $tabs->addTab(
                        'tvs',
                        __('global.settings_templvars'),
                        slot: $tabTvs
                    )
                )
                ->when(
                    $groupTv == 5,
                    function (Tabs $tabs) use ($tabTvs) {
                        array_map(
                            fn($tab) => $tabs->addTab(
                                $tab['id'],
                                $tab['name'],
                                slot: $tabTvs['slots'][$tab['id']],
                            ),
                            $tabTvs['attrs']['data']
                        );

                        return $tabs;
                    }
                )
                ->when(
                    config('global.use_udperms'),
                    fn(Tabs $tabs) => $this->tabPermissions($tabs)
                ),

            Crumbs::make()->setData($breadcrumbs),
        ];
    }

    /**
     * @param Collection $tvs
     *
     * @return Tabs
     */
    protected function tabTvs(Collection $tvs): Tabs
    {
        $tvTabs = Tabs::make()
            ->setId('tvs')
            ->setData([])
            ->isVertical();

        $components = [
            'text'         => Input::class,
            'number'       => Number::class,
            'email'        => Email::class,
            'date'         => DateTime::class,
            'dropdown'     => Select::class,
            'checkbox'     => Checkbox::class,
            'option'       => Radio::class,
            'textarea'     => Textarea::class,
            'textareamini' => Textarea::class,
            'richtext'     => CodeEditor::class,
            'file'         => File::class,
            'image'        => File::class,
        ];

        /** @var SiteTmplvar $tv */
        foreach ($tvs as $tv) {
            $categoryId = 'category-' . $tv->category;

            $tvTabs->addTab(
                $categoryId,
                $tv->category ? $tv->category()->first()->category : __('global.no_category'),
            );

            $data = array_map(function ($item) {
                if (stripos($item, '==')) {
                    [$value, $key] = explode('==', $item);
                } else {
                    $value = $key = $item;
                }

                return [
                    'key'   => $key,
                    'value' => $value,
                ];
            }, explode('||', (string) $tv->elements));

            if (str_starts_with($tv->type, 'custom_tv:')) {
                $tvTabs->putSlot(
                    $categoryId,
                    Textarea::make('data.tvs.' . $tv->name)
                        ->setData($data)
                        ->setLabel($tv->caption)
                        ->setDescription($tv['description'])
                        ->setHelp(
                            '<b>[*' . $tv->name . '*]</b> <sup>' . $tv->id . '</sup><br>' .
                            $tv->description
                        )
                        ->setRows(5)
                        ->setAttribute('style', ['margin-bottom' => '1rem']),
                );
            } else {
                /** @var Field $field */
                $field = app($components[$tv->type] ?? $components['text']);

                $tvTabs->putSlot(
                    $categoryId,
                    $field
                        ->setModel('data.tvs.' . $tv->name)
                        ->setData($data)
                        ->setLabel($tv->caption)
                        ->setDescription($tv->description)
                        ->setHelp(
                            '<b>[*' . $tv->name . '*]</b> <sup>' . $tv['id'] . '</sup><br>' .
                            $tv->description
                        )
                        ->when(
                            in_array($tv->type, ['file', 'image']),
                            fn(Field $field) => $field
                                ->setEmitClick('modal:component')
                                ->setUrl(route('manager.api.filemanager.index', ['type' => $tv->type]))
                        )
                        ->setAttribute('style', ['margin-bottom' => '1rem'])
                );
            }
        }

        return $tvTabs;
    }

    /**
     * @param Tabs $tabs
     *
     * @return Tabs
     */
    protected function tabPermissions(Tabs $tabs): Tabs
    {
        return $tabs
            ->addTab(
                'permissions',
                __('global.access_permissions'),
                slot: [
                    __('global.access_permissions_docs_message') . '<br/><br/>',

                    Checkbox::make('data.is_document_group')
                        ->setLabel(__('global.all_doc_groups'))
                        ->setCheckedValue(true, false)
                        ->setRelation('data.document_groups', [], [], true)
                        ->setAttribute('style', ['margin-bottom' => '1rem']),

                    Checkbox::make('data.document_groups')
                        ->setLabel(__('global.access_permissions_resource_groups'))
                        ->setData(
                            DocumentgroupName::all()
                                ->map(fn(DocumentgroupName $group) => [
                                    'key'   => $group->getKey(),
                                    'value' => $group->name,
                                ])
                                ->toArray()
                        )
                        ->setRelation('data.is_document_group', false, true)
                        ->setAttribute('style', ['margin-bottom' => '1rem']),
                ]
            );
    }

    /**
     * @return array
     */
    public function tree(): array
    {
        return Tab::make()
            ->setId('resources')
            ->setTitle(__('global.manage_documents'))
            ->setIcon('fa fa-sitemap')
            ->setPermissions('edit_document,view_document')
            ->setRoute('/resource/:id')
            ->setSlot(
                Tree::make()
                    ->setId('resources')
                    ->setRoute('/resource/:id')
                    ->setRouteList('Resources')
                    ->setUrl('/resource/tree')
                    ->setAppends(['id'])
                    ->setAliases([
                        'selected' => 'hidemenu:0',
                        'deleted'  => 'deleted:1',
                        'muted'    => 'published:0',
                    ])
                    ->setIcons([
                        'default'                              => 'far fa-file',
                        config('global.unauthorized_page')     => 'fa fa-lock text-rose-600',
                        config('global.site_start')            => 'fa fa-home text-blue-500',
                        config('global.site_unavailable_page') => 'fa fa-ban text-amber-400',
                        config('global.error_page')            => 'fa fa-exclamation-triangle text-rose-600',
                        'reference'                            => 'fa fa-link',
                    ])
                    ->setTemplates([
                        'title' =>
                            __('global.pagetitle') . ': {title}' . PHP_EOL .
                            __('global.id') . ': {id}' . PHP_EOL .
                            __('global.resource_opt_menu_title') . ': {attributes.menutitle}' . PHP_EOL .
                            __('global.resource_opt_menu_index') . ': {attributes.menuindex}' . PHP_EOL .
                            __('global.alias') . ': {attributes.alias}' . PHP_EOL .
                            __('global.template') . ': {attributes.template}' . PHP_EOL .
                            __('global.resource_opt_richtext') . ': {attributes.richtext}' . PHP_EOL .
                            __('global.page_data_searchable') . ': {attributes.searchable}' . PHP_EOL .
                            __('global.page_data_cacheable') . ': {attributes.cacheable}' . PHP_EOL,
                    ])
                    ->setContextMenu([
                        'class'   => 'text-base',
                        'actions' => [
                            [
                                'title' => __('global.create_resource_here'),
                                'icon'  => 'fa fa-file',
                                'to'    => [
                                    'path'  => '/resource/0',
                                    'query' => [
                                        'type'   => 'resource',
                                        'parent' => ':id',
                                    ],
                                ],
                            ],
                            [
                                'title' => __('global.create_weblink_here'),
                                'icon'  => 'fa fa-link',
                                'to'    => [
                                    'path'  => '/resource/0',
                                    'query' => [
                                        'type'   => 'reference',
                                        'parent' => ':id',
                                    ],
                                ],
                            ],
                            [
                                'title' => __('global.edit'),
                                'icon'  => 'fa fa-edit',
                                'to'    => [
                                    'path' => '/resource/:id',
                                ],
                            ],
                            [
                                'title' => __('global.move'),
                                'icon'  => 'fa fa-arrows',
                            ],
                            [
                                'title' => __('global.duplicate'),
                                'icon'  => 'fa fa-clone',
                            ],
                            [
                                'split' => true,
                            ],
                            [
                                'title'  => __('global.sort_menuindex'),
                                'icon'   => 'fa fa-sort-numeric-asc',
                                'hidden' => [
                                    'isfolder' => 0,
                                ],
                            ],
                            [
                                'title'  => __('global.unpublish_resource'),
                                'icon'   => 'fa fa-close',
                                'hidden' => [
                                    'published' => 0,
                                ],
                            ],
                            [
                                'title'  => __('global.publish_resource'),
                                'icon'   => 'fa fa-rotate-left',
                                'hidden' => [
                                    'published' => 1,
                                ],
                            ],
                            [
                                'title'  => __('global.delete'),
                                'icon'   => 'fa fa-trash',
                                'hidden' => [
                                    'deleted' => 1,
                                ],
                            ],
                            [
                                'split' => true,
                            ],
                            [
                                'title'  => __('global.undelete_resource'),
                                'icon'   => 'fa fa-undo',
                                'hidden' => [
                                    'deleted' => 0,
                                ],
                            ],
                            [
                                'title' => __('global.resource_overview'),
                                'icon'  => 'fa fa-info',
                                'to'    => [
                                    'path' => '/resources/:id',
                                ],
                            ],
                            [
                                'title' => __('global.preview'),
                                'icon'  => 'fa fa-eye',
                                'to'    => [
                                    'path'   => '/preview/:id',
                                    'target' => '_blank',
                                ],
                            ],
                        ],
                    ])
                    ->setMenu([
                        'actions' => [
                            [
                                'icon'   => 'fa fa-refresh',
                                'click'  => 'update',
                                'loader' => true,
                            ],
                            [
                                'icon'  => 'fa fa-file-circle-plus',
                                'title' => __('global.new_resource'),
                                'to'    => [
                                    'path'  => '/resource/0',
                                    'query' => [
                                        'type' => 'document',
                                    ],
                                ],
                            ],
                            [
                                'icon'  => 'fa fa-link',
                                'title' => __('global.add_weblink'),
                                'to'    => [
                                    'path'  => '/resource/0',
                                    'query' => [
                                        'type' => 'reference',
                                    ],
                                ],
                            ],
                            [
                                'icon'     => 'fa fa-sort',
                                'title'    => __('global.sort_tree'),
                                'position' => 'right',
                                'actions'  => [
                                    [
                                        'title' => __('global.sort_tree'),
                                    ],
                                    [
                                        'key'    => 'dir',
                                        'value'  => 'asc',
                                        'title'  => __('global.sort_asc'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'dir',
                                        'value'  => 'desc',
                                        'title'  => __('global.sort_desc'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'split' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'id',
                                        'title'  => 'ID',
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'menuindex',
                                        'title'  => __('global.resource_opt_menu_index'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'isfolder',
                                        'title'  => __('global.folder'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'pagetitle',
                                        'title'  => __('global.pagetitle'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'longtitle',
                                        'title'  => __('global.long_title'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'alias',
                                        'title'  => __('global.alias'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'createdon',
                                        'title'  => __('global.createdon'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'editedon',
                                        'title'  => __('global.editedon'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key'    => 'order',
                                        'value'  => 'publishedon',
                                        'title'  => __('global.publish_date'),
                                        'toggle' => true,
                                    ],
                                ],
                            ],
                            [
                                'icon'     => 'fa fa-eye',
                                'position' => 'right',
                                'actions'  => [
                                    [
                                        'title' => __('global.setting_resource_tree_node_name'),
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'pagetitle',
                                        'title'  => __('global.pagetitle'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'longtitle',
                                        'title'  => __('global.long_title'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'menutitle',
                                        'title'  => __('global.resource_opt_menu_title'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'alias',
                                        'title'  => __('global.alias'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'createdon',
                                        'title'  => __('global.createdon'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'editedon',
                                        'title'  => __('global.editedon'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                    [
                                        'key'    => 'keyTitle',
                                        'value'  => 'publishedon',
                                        'title'  => __('global.publish_date'),
                                        'toggle' => true,
                                        'click'  => 'changeKeyTitle',
                                    ],
                                ],
                            ],
                            [
                                'component' => 'search',
                            ],
                        ],
                    ])
                    ->setSettings([
                        'parent'   => -1,
                        'dir'      => 'asc',
                        'order'    => 'menuindex',
                        'keyTitle' => 'pagetitle',
                    ])
            )
            ->toArray();
    }
}
