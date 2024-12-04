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
use Illuminate\Support\Facades\URL;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Checkbox;
use Team64j\LaravelManagerComponents\CodeEditor;
use Team64j\LaravelManagerComponents\Crumbs;
use Team64j\LaravelManagerComponents\DateTime;
use Team64j\LaravelManagerComponents\Email;
use Team64j\LaravelManagerComponents\Field;
use Team64j\LaravelManagerComponents\File;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Input;
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
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.new_resource');
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
     *
     * @return array
     */
    public function default(SiteContent $model = null): array
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
                'data.attributes.content',
                Lang::get('global.weblink'),
                '<b>[*content*]</b><br>' . Lang::get('global.resource_weblink_help'),
                'mb-0'
            );

            $title = Lang::get('global.untitled_weblink');
        } else {
            $filedContent = CodeEditor::make(
                'data.attributes.content',
                Lang::get('global.resource_content'),
                '<b>[*content*]</b>',
                'mb-0'
            )
                ->setRows(20)
                ->setLanguage('html');

            $title = $this->title();
        }

        $tvs = $model->getTvs();
        $tabTvs = $this->tabTvs($tvs);
        $groupTv = $tvs->count() ? Config::get('global.group_tvs') : '';
        $route = URL::getRouteById($model->getKey());

        return [
            GlobalTab::make(
                $this->icon(),
                $this->title($model->pagetitle),
            ),

            Actions::make()
                ->setCancelTo([
                    'path' => '/resources/' . $model->parent,
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
                            Lang::get('global.undelete_resource'),
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
                                Lang::get('global.delete'),
                                null,
                                'btn-red',
                                'fa fa-trash-alt'
                            )
                            ->setCopy(to: ['path' => '/resource/0?id=' . $model->getKey()]),
                    )
                )
                ->setSaveAnd(),

            Title::make()
                ->setModel('data.attributes.pagetitle')
                ->setTitle($title)
                ->setIcon($this->icon())
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('resource')
                ->addTab(
                    'general',
                    Lang::get('global.settings_general'),
                    slot: [
                        Template::make()
                            ->setClass('flex flex-wrap grow lg:basis-2/3 xl:basis-9/12 p-5')
                            ->setSlot([
                                Input::make(
                                    'data.attributes.pagetitle',
                                    Lang::get('global.resource_title'),
                                    '<b>[*pagetitle*]</b><br>' . Lang::get('global.resource_title_help'),
                                    'mb-3 lg:pr-2 lg:basis-2/3'
                                )
                                    ->isRequired(),

                                Input::make(
                                    'data.attributes.alias',
                                    Lang::get('global.resource_alias'),
                                    '<b>[*alias*]</b><br>' . Lang::get('global.resource_alias_help'),
                                    'mb-3 lg:pl-2 lg:basis-1/3'
                                )
                                    ->isRequired(),

                                Input::make(
                                    'data.attributes.longtitle',
                                    Lang::get('global.long_title'),
                                    '<b>[*longtitle*]</b><br>' . Lang::get('global.resource_long_title_help'),
                                    'mb-3'
                                ),

                                Textarea::make(
                                    'data.attributes.description',
                                    Lang::get('global.resource_description'),
                                    '<b>[*description*]</b><br>' . Lang::get('global.resource_description_help'),
                                    'mb-3 lg:pr-2 lg:basis-1/2'
                                )
                                    ->setRows(3),

                                CodeEditor::make(
                                    'data.attributes.introtext',
                                    Lang::get('global.resource_summary'),
                                    '<b>[*introtext*]</b><br>' . Lang::get('global.resource_summary_help'),
                                    'mb-3 lg:pl-2 lg:basis-1/2'
                                )
                                    ->setRows(3)
                                    ->setLanguage('html'),

                                $filedContent,
                            ]),

                        Template::make()
                            ->setClass('flex flex-wrap grow lg:basis-1/3 xl:basis-3/12 p-5')
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
                                    'data.attributes.parent',
                                    Lang::get('global.import_parent_resource'),
                                    '<b>[*parent*]</b><br>' . Lang::get('global.resource_parent_help'),
                                    'mb-3'
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
                                    'data.attributes.template',
                                    Lang::get('global.page_data_template'),
                                    '<b>[*template*]</b><br>' . Lang::get('global.page_data_template_help'),
                                    'mb-3'
                                )
                                    ->setUrl('/templates/select')
                                    ->setData([
                                        [
                                            'key' => $model->template ?? 0,
                                            'value' => ($model->tpl->templatename ?? 'blank') . ' (' .
                                                ($model->template ?? 0) . ')',
                                            'selected' => true,
                                        ],
                                    ])
                                    ->setEmitInput('inputChangeQuery'),

                                Checkbox::make(
                                    'data.attributes.hidemenu',
                                    Lang::get('global.resource_opt_show_menu'),
                                    '<b>[*hidemenu*]</b><br>' . Lang::get('global.resource_opt_show_menu_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(0, 1),

                                Number::make(
                                    'data.attributes.menuindex',
                                    Lang::get('global.resource_opt_menu_index'),
                                    '<b>[*menuindex*]</b><br>' . Lang::get('global.resource_opt_menu_index_help'),
                                    'mb-3'
                                ),

                                Input::make(
                                    'data.attributes.menutitle',
                                    Lang::get('global.resource_opt_menu_title'),
                                    '<b>[*menutitle*]</b><br>' . Lang::get('global.resource_opt_menu_title_help'),
                                    'mb-3'
                                ),

                                Input::make(
                                    'data.attributes.link_attributes',
                                    Lang::get('global.link_attributes'),
                                    '<b>[*link_attributes*]</b><br>' . Lang::get('global.link_attributes_help'),
                                    'mb-3'
                                ),

                                Checkbox::make(
                                    'data.attributes.published',
                                    Lang::get('global.resource_opt_published'),
                                    '<b>[*published*]</b><br>' . Lang::get('global.resource_opt_published_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                DateTime::make(
                                    'data.attributes.publishedon',
                                    Lang::get('global.page_data_published'),
                                    '',
                                    'mb-3'
                                )->isClear(),

                                DateTime::make(
                                    'data.attributes.pub_date',
                                    Lang::get('global.page_data_publishdate'),
                                    '<b>[*pub_date*]</b><br>' . Lang::get('global.page_data_publishdate_help'),
                                    'mb-3'
                                )->isClear(),

                                DateTime::make(
                                    'data.attributes.unpub_date',
                                    Lang::get('global.page_data_unpublishdate'),
                                    '<b>[*unpub_date*]</b><br>' . Lang::get('global.page_data_unpublishdate_help')
                                )->isClear(),
                            ]),
                    ]
                )
                ->when(
                    $groupTv == 0,
                    fn(Tabs $tabs) => $tabs->putSlot(
                        'general',
                        Template::make()
                            ->setClass('grow p-5')
                            ->setSlot(Arr::flatten($tabTvs['slots']))
                    )
                )
                ->when(
                    $groupTv == 1,
                    fn(Tabs $tabs) => $tabs->putSlot(
                        'general',
                        array_map(
                            fn($slot) => Section::make()
                                ->setClass('p-5')
                                ->setLabel($slot['name'])
                                ->setSlot($tabTvs['slots'][$slot['id']])
                                ->isExpanded(),
                            $tabTvs['attrs']['data']
                        )
                    )
                )
                ->when(
                    $groupTv == 2,
                    fn(Tabs $tabs) => $tabs->putSlot('general', $tabTvs)
                )
                ->addTab(
                    'settings',
                    Lang::get('global.settings_page_settings'),
                    slot: [
                        Template::make()
                            ->setClass('flex flex-wrap grow lg:basis-1/2 p-5')
                            ->setSlot([
                                Select::make(
                                    'data.attributes.type',
                                    Lang::get('global.resource_type'),
                                    '<b>[*type*]</b><br>' . Lang::get('global.resource_type_message'),
                                    'mb-3'
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
                                    'data.attributes.contentType',
                                    Lang::get('global.page_data_contentType'),
                                    '<b>[*contentType*]</b><br>' . Lang::get('global.page_data_contentType_help'),
                                    'mb-3'
                                )
                                    ->setData(
                                        array_map(fn($k) => [
                                            'key' => $k,
                                            'value' => $k,
                                        ], explode(',', Config::get('global.custom_contenttype', 'text/html')))
                                    ),

                                Select::make(
                                    'data.attributes.content_dispo',
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
                            ->setClass('flex flex-wrap grow lg:basis-1/2 p-5')
                            ->setSlot([
                                Checkbox::make(
                                    'data.attributes.isfolder',
                                    Lang::get('global.resource_opt_folder'),
                                    '<b>[*isfolder*]</b><br>' . Lang::get('global.resource_opt_folder_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                Checkbox::make(
                                    'data.attributes.hide_from_tree',
                                    Lang::get('global.track_visitors_title'),
                                    '<b>[*hide_from_tree*]</b><br>' .
                                    Lang::get('global.resource_opt_trackvisit_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(0, 1),

                                Checkbox::make(
                                    'data.attributes.alias_visible',
                                    Lang::get('global.resource_opt_alvisibled'),
                                    '<b>[*alias_visible*]</b><br>' .
                                    Lang::get('global.resource_opt_alvisibled_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                Checkbox::make(
                                    'data.attributes.richtext',
                                    Lang::get('global.resource_opt_richtext'),
                                    '<b>[*richtext*]</b><br>' . Lang::get('global.resource_opt_richtext_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                Checkbox::make(
                                    'data.attributes.searchable',
                                    Lang::get('global.page_data_searchable'),
                                    '<b>[*searchable*]</b><br>' . Lang::get('global.page_data_searchable_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                Checkbox::make(
                                    'data.attributes.cacheable',
                                    Lang::get('global.page_data_cacheable'),
                                    '<b>[*cacheable*]</b><br>' . Lang::get('global.page_data_cacheable_help'),
                                    'mb-3'
                                )
                                    ->setCheckedValue(1, 0),

                                Checkbox::make(
                                    'data.attributes.empty_cache',
                                    Lang::get('global.resource_opt_emptycache'),
                                    Lang::get('global.resource_opt_emptycache_help')
                                )
                                    ->setCheckedValue(1, 0)
                                    ->setValue(1),
                            ]),
                    ]
                )
                ->when(
                    $groupTv == 3,
                    fn(Tabs $tabs) => $tabs->addTab(
                        'tvs',
                        Lang::get('global.settings_templvars'),
                        class: 'flex flex-wrap p-5',
                        slot: array_map(
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
                    $groupTv == 4,
                    fn(Tabs $tabs) => $tabs->addTab(
                        'tvs',
                        Lang::get('global.settings_templvars'),
                        slot: $tabTvs
                    )
                )
                ->when(
                    $groupTv == 5,
                    fn(Tabs $tabs) => array_map(
                        fn($tab) => $tabs->addTab(
                            $tab['id'],
                            $tab['name'],
                            class: 'p-5',
                            slot: $tabTvs['slots'][$tab['id']],
                        ),
                        $tabTvs['attrs']['data']
                    ),
                )
                ->when(
                    Config::get('global.use_udperms'),
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
            ->setClass('p-5')
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
            }, explode('||', (string) $tv['elements']));

            if (str_starts_with($tv['type'], 'custom_tv:')) {
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
                        ->setClass('mb-3')
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
                        ->setClass('mb-3')
                        ->when(
                            in_array($tv['type'], ['file', 'image']),
                            fn(Field $field) => $field
                                ->setEmitClick('modal:component')
                                ->setUrl(route('manager.api.filemanager.index', ['type' => $tv['type']]))
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
        return $tabs
            ->addTab(
                'permissions',
                Lang::get('global.access_permissions'),
                class: 'flex-col p-5',
                slot: [
                    Lang::get('global.access_permissions_docs_message') . '<br/><br/>',

                    Checkbox::make()
                        ->setModel('data.is_document_group')
                        ->setLabel(Lang::get('global.all_doc_groups'))
                        ->setCheckedValue(true, false)
                        ->setRelation('data.document_groups', [], [], true)
                        ->setClass('mb-3'),

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
                        ->setRelation('data.is_document_group', false, true)
                        ->setClass('mb-3'),
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
                                    'path' => '/resource/0',
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
                                    'path' => '/resource/0',
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
                                    'path' => '/resource/0',
                                    'query' => [
                                        'type' => 'document',
                                    ],
                                ],
                            ],
                            [
                                'icon' => 'fa fa-link',
                                'to' => [
                                    'path' => '/resource/0',
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
