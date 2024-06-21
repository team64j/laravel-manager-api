<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use EvolutionCMS\Models\DocumentgroupName;
use EvolutionCMS\Models\SiteContent;
use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\ActionsButtons;
use Team64j\LaravelManagerComponents\Breadcrumbs;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\DateTime;
use Team64j\LaravelManagerComponents\Email;
use Team64j\LaravelManagerComponents\Field;
use Team64j\LaravelManagerComponents\File;
use Team64j\LaravelManagerComponents\Input;
use Team64j\LaravelManagerComponents\Main;
use Team64j\LaravelManagerComponents\Number;
use Team64j\LaravelManagerComponents\Radio;
use Team64j\LaravelManagerComponents\Section;
use Team64j\LaravelManagerComponents\Select;
use Team64j\LaravelManagerComponents\Tab;
use Team64j\LaravelManagerComponents\Tabs;
use Team64j\LaravelManagerComponents\Template;
use Team64j\LaravelManagerComponents\Textarea;
use Team64j\LaravelManagerComponents\Title;
use Team64j\LaravelManagerComponents\Tree;

class ResourceLayout extends Layout
{
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
                'id' => 0,
                'parent' => null,
                'title' => 'root',
                'to' => [
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
                    'id' => $item['id'],
                    'parent' => $item['parent'],
                    'title' => $item['pagetitle'],
                    'to' => [
                        'path' => '/resource/' . $item['id'],
                    ],
                ];
            },
            $items
        );
    }

    /**
     * @param SiteContent|null $model
     * @param string $url
     *
     * @return array
     */
    public function default(SiteContent $model = null, string $url = ''): array
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
                        'id' => $item->getKey(),
                        'parent' => $item->parent,
                        'name' => $item->pagetitle,
                        'to' => '/resource/' . $item->getKey(),
                        'tooltip' => 'ID: ' . $item->getKey() . '<br>' . $item->pagetitle,
                    ],
                    $model->parents ? iterator_to_array(flatten([$model->parents])) : []
                ),
                [
                    [
                        'id' => 0,
                        'parent' => null,
                        'name' => Lang::get('global.manage_documents'),
                        'to' => '/resources/0',
                        'tooltip' => 'ID: 0<br>root',
                    ],
                ]
            )
        );

        if (request()->input('type') == 'reference') {
            $filedContent = Input::make(
                'content',
                Lang::get('global.weblink'),
                '<b>[*content*]</b><br>' . Lang::get('global.resource_weblink_help')
            );

            $title = Lang::get('global.untitled_weblink');
        } else {
            $filedContent = CodeEditor::make(
                'content',
                Lang::get('global.resource_content'),
                '<b>[*content*]</b>'
            )
                ->setRows(20)
                ->setLanguage('html');

            $title = Lang::get('global.new_resource');
        }

        $tvs = $model->getTvs();
        $tabTvs = $this->tabTvs($tvs);

        return Main::make()
            ->setActions(
                fn(ActionsButtons $component) => $component
                    ->setCancelTo([
                        'path' => '/resources/' . $model->parent,
                        'close' => true,
                    ])
                    ->setViewTo(['href' => $url])
                    ->when(
                        $model->deleted,
                        fn(ActionsButtons $component) => $component
                            ->setAction(
                                [
                                    'action' => 'custom:update',
                                    'method' => 'patch',
                                    'url' => '/resource/:id',
                                    'params' => [
                                        'deleted' => 0,
                                    ],
                                ],
                                Lang::get('global.undelete_resource'),
                                null,
                                'btn-blue',
                                'fa fa-undo'
                            ),
                        fn(ActionsButtons $component) => $component->when(
                            $model->getKey(),
                            fn(ActionsButtons $component) => $component
                                ->setAction(
                                    [
                                        'action' => 'custom:update',
                                        'method' => 'patch',
                                        'url' => '/resource/:id',
                                        'params' => [
                                            'deleted' => 1,
                                        ],
                                    ],
                                    Lang::get('global.delete'),
                                    null,
                                    'btn-red',
                                    'fa fa-trash-alt'
                                )
                                ->setCopy()
                        )
                    )
                    ->setSaveAnd()
            )
            ->setTitle(
                fn(Title $component) => $component
                    ->setModel('pagetitle')
                    ->setTitle($title)
                    ->setIcon('fa fa-edit')
                    ->setId($model->getKey())
            )
            ->setTabs(
                fn(Tabs $component) => $component
                    ->setId('resource')
                    ->addTab('general', Lang::get('global.settings_general'))
                    ->addSlot(
                        'general',
                        array_merge([
                            Template::make()
                                ->setClass('flex flex-wrap grow lg:basis-2/3 xl:basis-9/12 lg:pr-6')
                                ->setSlot([
                                    Input::make(
                                        'pagetitle',
                                        Lang::get('global.resource_title'),
                                        '<b>[*pagetitle*]</b><br>' . Lang::get('global.resource_title_help'),
                                        'lg:pr-2 lg:basis-2/3'
                                    )
                                        ->isRequired(),

                                    Input::make(
                                        'alias',
                                        Lang::get('global.resource_alias'),
                                        '<b>[*alias*]</b><br>' . Lang::get('global.resource_alias_help'),
                                        'lg:pl-2 lg:basis-1/3'
                                    )
                                        ->isRequired(),

                                    Input::make(
                                        'longtitle',
                                        Lang::get('global.long_title'),
                                        '<b>[*longtitle*]</b><br>' . Lang::get('global.resource_long_title_help')
                                    ),

                                    Textarea::make(
                                        'description',
                                        Lang::get('global.resource_description'),
                                        '<b>[*description*]</b><br>' . Lang::get('global.resource_description_help'),
                                        'lg:pr-2 lg:basis-1/2'
                                    )
                                        ->setRows(3),

                                    CodeEditor::make(
                                        'introtext',
                                        Lang::get('global.resource_summary'),
                                        '<b>[*introtext*]</b><br>' . Lang::get('global.resource_summary_help'),
                                        'lg:pl-2 lg:basis-1/2'
                                    )
                                        ->setRows(3)
                                        ->setLanguage('html'),

                                    $filedContent,
                                ]),

                            Template::make()
                                ->setClass('flex flex-wrap grow lg:basis-1/3 xl:basis-3/12')
                                ->setSlot([
                                    /*Select::make(
                                        'parent',
                                        Lang::get('global.import_parent_resource'),
                                        '<b>[*parent*]</b><br>' . Lang::get('global.resource_parent_help')
                                    )
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

                                    Input::make(
                                        'parent',
                                        Lang::get('global.import_parent_resource'),
                                        '<b>[*parent*]</b><br>' . Lang::get('global.resource_parent_help')
                                    )
                                        ->setInputClass('cursor-pointer')
                                        ->setValue(
                                            $model->parent ? $model->parent . ' - ' . $model->parents->pagetitle
                                                : '0 - root'
                                        )
                                        ->setEmitClick('inputTreeSelect')
                                        ->isRequired()
                                        ->isReadonly(),

                                    Select::make(
                                        'template',
                                        Lang::get('global.page_data_template'),
                                        '<b>[*template*]</b><br>' . Lang::get('global.page_data_template_help')
                                    )
                                        ->setUrl('/templates/select')
                                        ->setData([
                                            [
                                                'key' => $model->template,
                                                'value' => $model->tpl->templatename ?? $model->template,
                                                'selected' => true,
                                            ],
                                        ])
                                        ->setEmitInput('inputChangeQuery'),

                                    Checkbox::make(
                                        'hidemenu',
                                        Lang::get('global.resource_opt_show_menu'),
                                        '<b>[*hidemenu*]</b><br>' . Lang::get('global.resource_opt_show_menu_help')
                                    )
                                        ->setCheckedValue(0, 1),

                                    Number::make(
                                        'menuindex',
                                        Lang::get('global.resource_opt_menu_index'),
                                        '<b>[*menuindex*]</b><br>' . Lang::get('global.resource_opt_menu_index_help')
                                    ),

                                    Input::make(
                                        'menutitle',
                                        Lang::get('global.resource_opt_menu_title'),
                                        '<b>[*menutitle*]</b><br>' . Lang::get('global.resource_opt_menu_title_help')
                                    ),

                                    Input::make(
                                        'link_attributes',
                                        Lang::get('global.link_attributes'),
                                        '<b>[*link_attributes*]</b><br>' . Lang::get('global.link_attributes_help')
                                    ),

                                    Checkbox::make(
                                        'published',
                                        Lang::get('global.resource_opt_published'),
                                        '<b>[*published*]</b><br>' . Lang::get('global.resource_opt_published_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    DateTime::make(
                                        'publishedon',
                                        Lang::get('global.page_data_published')
                                    )->setAttribute('clear', true),

                                    DateTime::make(
                                        'pub_date',
                                        Lang::get('global.page_data_publishdate'),
                                        '<b>[*pub_date*]</b><br>' . Lang::get('global.page_data_publishdate_help')
                                    )->setAttribute('clear', true),

                                    DateTime::make(
                                        'unpub_date',
                                        Lang::get('global.page_data_unpublishdate'),
                                        '<b>[*unpub_date*]</b><br>' . Lang::get('global.page_data_unpublishdate_help')
                                    )->setAttribute('clear', true),
                                ]),

                            //                            $tvs->count() && Config::get('global.group_tvs') == 0 ? Arr::flatten($tabTvs['slots'])
                            //                                : null,
                            //
                            //                            $tvs->count() && Config::get('global.group_tvs') == 1 ? array_map(
                            //                                fn($slot) => Section::make()
                            //                                    ->setLabel($slot['name'])
                            //                                    ->setSlot($tabTvs['slots'][$slot['id']])
                            //                                    ->setAttribute('expanded', true),
                            //                                $tabTvs['attrs']['data']
                            //                            ) : null,
                            //
                            //                            $tvs->count() && Config::get('global.group_tvs') == 2 ? $tabTvs->setAttribute(
                            //                                'vertical',
                            //                                false
                            //                            ) : null,
                        ],
                            $tvs->count() && Config::get('global.group_tvs') == 0 ? Arr::flatten($tabTvs['slots'])
                                : [],

                            $tvs->count() && Config::get('global.group_tvs') == 1 ? array_map(
                                fn($slot) => Section::make()
                                    ->setClass('!p-0')
                                    ->setLabel($slot['name'])
                                    ->setSlot($tabTvs['slots'][$slot['id']])
                                    ->isExpanded(),
                                $tabTvs['attrs']['data']
                            ) : [],

                            $tvs->count() && Config::get('global.group_tvs') == 2 ? [
                                $tabTvs->toArray(),
                            ] : [],
                        )
                    )
                    ->addTab('settings', Lang::get('global.settings_page_settings'))
                    ->addSlot(
                        'settings',
                        [
                            Template::make()
                                ->setClass('flex flex-wrap grow lg:basis-1/2 lg:pr-3')
                                ->setSlot([
                                    Select::make(
                                        'type',
                                        Lang::get('global.resource_type'),
                                        '<b>[*type*]</b><br>' . Lang::get('global.resource_type_message')
                                    )
                                        ->setData([
                                            [
                                                'key' => 'document',
                                                'value' => Lang::get('global.resource_type_webpage'),
                                            ],
                                            [
                                                'key' => 'reference',
                                                'value' => Lang::get('global.resource_type_weblink'),
                                            ],
                                        ])
                                        ->setEmitInput('inputChangeQuery'),

                                    Select::make(
                                        'contentType',
                                        Lang::get('global.page_data_contentType'),
                                        '<b>[*contentType*]</b><br>' . Lang::get('global.page_data_contentType_help')
                                    )
                                        ->setData(
                                            array_map(fn($k) => [
                                                'key' => $k,
                                                'value' => $k,
                                            ], explode(',', Config::get('global.custom_contenttype', 'text/html')))
                                        ),

                                    Select::make(
                                        'content_dispo',
                                        Lang::get('global.resource_opt_contentdispo'),
                                        '<b>[*content_dispo*]</b><br>' .
                                        Lang::get('global.resource_opt_contentdispo_help')
                                    )
                                        ->setData([
                                            [
                                                'key' => 0,
                                                'value' => Lang::get('global.inline'),
                                            ],
                                            [
                                                'key' => 1,
                                                'value' => Lang::get('global.attachment'),
                                            ],
                                        ]),
                                ]),

                            Template::make()
                                ->setClass('flex flex-wrap grow lg:basis-1/2 lg:pl-3')
                                ->setSlot([
                                    Checkbox::make(
                                        'isfolder',
                                        Lang::get('global.resource_opt_folder'),
                                        '<b>[*isfolder*]</b><br>' . Lang::get('global.resource_opt_folder_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make(
                                        'hide_from_tree',
                                        Lang::get('global.track_visitors_title'),
                                        '<b>[*hide_from_tree*]</b><br>' .
                                        Lang::get('global.resource_opt_trackvisit_help')
                                    )
                                        ->setCheckedValue(0, 1),

                                    Checkbox::make(
                                        'alias_visible',
                                        Lang::get('global.resource_opt_alvisibled'),
                                        '<b>[*alias_visible*]</b><br>' .
                                        Lang::get('global.resource_opt_alvisibled_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make(
                                        'richtext',
                                        Lang::get('global.resource_opt_richtext'),
                                        '<b>[*richtext*]</b><br>' . Lang::get('global.resource_opt_richtext_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make(
                                        'searchable',
                                        Lang::get('global.page_data_searchable'),
                                        '<b>[*searchable*]</b><br>' . Lang::get('global.page_data_searchable_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make(
                                        'cacheable',
                                        Lang::get('global.page_data_cacheable'),
                                        '<b>[*cacheable*]</b><br>' . Lang::get('global.page_data_cacheable_help')
                                    )
                                        ->setCheckedValue(1, 0),

                                    Checkbox::make(
                                        'empty_cache',
                                        Lang::get('global.resource_opt_emptycache'),
                                        Lang::get('global.resource_opt_emptycache_help')
                                    )
                                        ->setCheckedValue(1, 0)
                                        ->setValue(1),
                                ]),
                        ]
                    )
                    ->when(
                        $tvs->count() && Config::get('global.group_tvs') == 3,
                        fn(Tabs $tabs) => $tabs
                            ->addTab(
                                'tvs',
                                Lang::get('global.settings_templvars'),
                                null,
                                'flex flex-wrap'
                            )
                            ->addSlot(
                                'tvs',
                                array_map(
                                    fn($slot) => Section::make()
                                        ->setClass('!p-0')
                                        ->setLabel($slot['name'])
                                        ->setSlot($tabTvs['slots'][$slot['id']])
                                        ->isExpanded(),
                                    $tabTvs['attrs']['data']
                                )
                            )
                    )
                    ->when(
                        $tvs->count() && Config::get('global.group_tvs') == 4,
                        fn(Tabs $tabs) => $tabs
                            ->addTab(
                                'tvs',
                                Lang::get('global.settings_templvars'),
                                null,
                                'flex flex-wrap'
                            )
                            ->addSlot(
                                'tvs',
                                $tabTvs
                            )
                    )
                    ->when(
                        $tvs->count() && Config::get('global.group_tvs') == 5,
                        function (Tabs $tabs) use ($tabTvs) {
                            foreach ($tabTvs['attrs']['data'] as $tab) {
                                $tabs->addTab(
                                    $tab['id'],
                                    $tab['name'],
                                    slot: $tabTvs['slots'][$tab['id']],
                                );
                            }

                            return $tabs;
                        }
                    )
                    ->when(
                        Config::get('global.use_udperms'),
                        fn(Tabs $tabs) => $this->tabPermissions($tabs)
                    ),
            )
            ->setBreadcrumbs(
                fn(Breadcrumbs $component) => $component->setData($breadcrumbs)
            )
            ->toArray();
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-edit';
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
            'text' => Input::class,
            'number' => Number::class,
            'email' => Email::class,
            'date' => DateTime::class,
            'dropdown' => Select::class,
            'checkbox' => Checkbox::class,
            'option' => Radio::class,
            'textarea' => Textarea::class,
            'textareamini' => Textarea::class,
            'richtext' => CodeEditor::class,
            'file' => File::class,
            'image' => File::class,
        ];

        foreach ($tvs as $tv) {
            $categoryId = 'category-' . $tv['category'];

            $tvTabs->addTab(
                $categoryId,
                $tv['category_name']
            );

            $custom = str_starts_with($tv['type'], 'custom_tv:');

            $data = array_map(function ($item) {
                if (stripos($item, '==')) {
                    [$value, $key] = explode('==', $item);
                } else {
                    $value = $key = $item;
                }

                return [
                    'key' => $key,
                    'value' => $value,
                ];
            }, explode('||', $tv['elements']));

            if ($custom) {
                $tvTabs->putSlot(
                    $categoryId,
                    Textarea::make()
                        ->setModel('data.tvs.' . $tv['name'])
                        ->setData($data)
                        ->setLabel($tv['caption'])
                        ->setDescription($tv['description'])
                        ->setHelp(
                            '<b>[*' . $tv['name'] . '*]</b><i class="badge">' . $tv['id'] . '</i><br>' .
                            $tv['description']
                        )
                        ->setRows(5)
                );
            } else {
                /** @var Field $field */
                $field = app($components[$tv['type']] ?? $components['text']);

                $tvTabs->putSlot(
                    $categoryId,
                    $field
                        ->setModel('data.tvs.' . $tv['name'])
                        ->setData($data)
                        ->setLabel($tv['caption'])
                        ->setDescription($tv['description'])
                        ->setHelp(
                            '<b>[*' . $tv['name'] . '*]</b><i class="badge">' . $tv['id'] . '</i><br>' .
                            $tv['description']
                        )
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
        $groups = DocumentgroupName::all()
            ->map(fn(DocumentgroupName $group) => [
                'key' => $group->getKey(),
                'value' => $group->name,
            ])
            ->toArray();

        return $tabs
            ->addTab('permissions', Lang::get('global.access_permissions'), null, 'flex-col')
            ->addSlot(
                'permissions',
                [
                    '<div class="pb-4 w-full">' . Lang::get('global.access_permissions_docs_message') . '</div>',

                    Checkbox::make()
                        ->setModel('data.is_document_group')
                        ->setLabel(Lang::get('global.all_doc_groups'))
                        ->setCheckedValue(true, false)
                        ->setRelation('data.document_groups', [], [], true),

                    Checkbox::make()
                        ->setModel('data.document_groups')
                        ->setLabel(Lang::get('global.access_permissions_resource_groups'))
                        ->setData($groups)
                        ->setRelation('data.is_document_group', false, true),
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
            ->setTitle(Lang::get('global.manage_documents'))
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
                    ->setIcons([
                        'default' => 'far fa-file',
                        Config::get('global.unauthorized_page') => 'fa fa-lock text-rose-600',
                        Config::get('global.site_start') => 'fa fa-home text-blue-500',
                        Config::get('global.site_unavailable_page') => 'fa fa-ban text-amber-400',
                        Config::get('global.error_page') => 'fa fa-exclamation-triangle text-rose-600',
                        'reference' => 'fa fa-link',
                    ])
                    ->setAliases([
                        'selected' => 'hidemenu:0',
                        'muted' => 'published:0',
                    ])
                    ->setTemplates([
                        'title' =>
                            Lang::get('global.pagetitle') . ': {title}' . PHP_EOL .
                            Lang::get('global.id') . ': {id}' . PHP_EOL .
                            Lang::get('global.resource_opt_menu_title') . ': {menutitle}' . PHP_EOL .
                            Lang::get('global.resource_opt_menu_index') . ': {menuindex}' . PHP_EOL .
                            Lang::get('global.alias') . ': {alias}' . PHP_EOL .
                            Lang::get('global.template') . ': {template}' . PHP_EOL .
                            Lang::get('global.resource_opt_richtext') . ': {richtext}' . PHP_EOL .
                            Lang::get('global.page_data_searchable') . ': {searchable}' . PHP_EOL .
                            Lang::get('global.page_data_cacheable') . ': {cacheable}' . PHP_EOL,
                    ])
                    ->setContextMenu([
                        'class' => 'text-base',
                        'actions' => [
                            [
                                'title' => Lang::get('global.create_resource_here'),
                                'icon' => 'fa fa-file',
                                'to' => [
                                    'path' => '/resource/new',
                                    'query' => [
                                        'type' => 'resource',
                                        'id' => ':id',
                                    ],
                                ],
                            ],
                            [
                                'title' => Lang::get('global.create_weblink_here'),
                                'icon' => 'fa fa-link',
                                'to' => [
                                    'path' => '/resource/new',
                                    'query' => [
                                        'type' => 'reference',
                                        'id' => ':id',
                                    ],
                                ],
                            ],
                            [
                                'title' => Lang::get('global.edit'),
                                'icon' => 'fa fa-edit',
                                'to' => [
                                    'path' => '/resource/:id',
                                ],
                            ],
                            [
                                'title' => Lang::get('global.move'),
                                'icon' => 'fa fa-arrows',
                            ],
                            [
                                'title' => Lang::get('global.duplicate'),
                                'icon' => 'fa fa-clone',
                            ],
                            [
                                'split' => true,
                            ],
                            [
                                'title' => Lang::get('global.sort_menuindex'),
                                'icon' => 'fa fa-sort-numeric-asc',
                                'hidden' => [
                                    'isfolder' => 0,
                                ],
                            ],
                            [
                                'title' => Lang::get('global.unpublish_resource'),
                                'icon' => 'fa fa-close',
                                'hidden' => [
                                    'published' => 0,
                                ],
                            ],
                            [
                                'title' => Lang::get('global.publish_resource'),
                                'icon' => 'fa fa-rotate-left',
                                'hidden' => [
                                    'published' => 1,
                                ],
                            ],
                            [
                                'title' => Lang::get('global.delete'),
                                'icon' => 'fa fa-trash',
                                'hidden' => [
                                    'deleted' => 1,
                                ],
                            ],
                            [
                                'split' => true,
                            ],
                            [
                                'title' => Lang::get('global.undelete_resource'),
                                'icon' => 'fa fa-undo',
                                'hidden' => [
                                    'deleted' => 0,
                                ],
                            ],
                            [
                                'title' => Lang::get('global.resource_overview'),
                                'icon' => 'fa fa-info',
                                'to' => [
                                    'path' => '/resources/:id',
                                ],
                            ],
                            [
                                'title' => Lang::get('global.preview'),
                                'icon' => 'fa fa-eye',
                                'to' => [
                                    'path' => '/preview/:id',
                                    'target' => '_blank',
                                ],
                            ],
                        ],
                    ])
                    ->setMenu([
                        'actions' => [
                            [
                                'icon' => 'fa fa-refresh',
                                'click' => 'update',
                                'loader' => true,
                            ],
                            [
                                'icon' => 'fa fa-file-circle-plus',
                                'to' => [
                                    'path' => '/resource/new',
                                    'query' => [
                                        'type' => 'document',
                                    ],
                                ],
                            ],
                            [
                                'icon' => 'fa fa-link',
                                'to' => [
                                    'path' => '/resource/new',
                                    'query' => [
                                        'type' => 'reference',
                                    ],
                                ],
                            ],
                            [
                                'icon' => 'fa fa-sort',
                                'position' => 'right',
                                'actions' => [
                                    [
                                        'title' => Lang::get('global.sort_tree'),
                                    ],
                                    [
                                        'key' => 'dir',
                                        'value' => 'asc',
                                        'title' => Lang::get('global.sort_asc'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'dir',
                                        'value' => 'desc',
                                        'title' => Lang::get('global.sort_desc'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'split' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'id',
                                        'title' => 'ID',
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'menuindex',
                                        'title' => Lang::get('global.resource_opt_menu_index'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'isfolder',
                                        'title' => Lang::get('global.folder'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'pagetitle',
                                        'title' => Lang::get('global.pagetitle'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'longtitle',
                                        'title' => Lang::get('global.long_title'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'alias',
                                        'title' => Lang::get('global.alias'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'createdon',
                                        'title' => Lang::get('global.createdon'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'editedon',
                                        'title' => Lang::get('global.editedon'),
                                        'toggle' => true,
                                    ],
                                    [
                                        'key' => 'order',
                                        'value' => 'publishedon',
                                        'title' => Lang::get('global.publish_date'),
                                        'toggle' => true,
                                    ],
                                ],
                            ],
                            [
                                'icon' => 'fa fa-eye',
                                'position' => 'right',
                                'actions' => [
                                    [
                                        'title' => Lang::get('global.setting_resource_tree_node_name'),
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'pagetitle',
                                        'title' => Lang::get('global.pagetitle'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'longtitle',
                                        'title' => Lang::get('global.long_title'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'menutitle',
                                        'title' => Lang::get('global.resource_opt_menu_title'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'alias',
                                        'title' => Lang::get('global.alias'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'createdon',
                                        'title' => Lang::get('global.createdon'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'editedon',
                                        'title' => Lang::get('global.editedon'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                    [
                                        'key' => 'keyTitle',
                                        'value' => 'publishedon',
                                        'title' => Lang::get('global.publish_date'),
                                        'toggle' => true,
                                        'click' => 'changeKeyTitle',
                                    ],
                                ],
                            ],
                            [
                                'component' => 'search',
                            ],
                        ],
                    ])
                    ->setSettings([
                        'parent' => -1,
                        'dir' => 'asc',
                        'order' => 'menuindex',
                        'keyTitle' => 'pagetitle',
                    ])
            )
            ->toArray();
    }
}
