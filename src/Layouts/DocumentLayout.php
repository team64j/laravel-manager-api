<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\DocumentgroupName;
use Team64j\LaravelEvolution\Models\SiteContent;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Components\CodeEditor;
use Team64j\LaravelManagerApi\Components\DateTime;
use Team64j\LaravelManagerApi\Components\Email;
use Team64j\LaravelManagerApi\Components\Field;
use Team64j\LaravelManagerApi\Components\File;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Number;
use Team64j\LaravelManagerApi\Components\Radio;
use Team64j\LaravelManagerApi\Components\Select;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Template;
use Team64j\LaravelManagerApi\Components\Textarea;
use Team64j\LaravelManagerApi\Components\Title;

class DocumentLayout extends Layout
{
    /**
     * @param SiteContent|null $model
     *
     * @return array
     */
    public function default(SiteContent $model = null): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setSaveAnd()
                ->if(
                    $model->deleted,
                    fn(ActionsButtons $actions) => $actions->setView()->setRestore(),
                    fn(ActionsButtons $actions) => $actions->if(
                        $model->getKey(),
                        fn(ActionsButtons $actions) => $actions->setView()->setDelete()->setCopy()
                    )
                ),

            Title::make()
                ->setModel('pagetitle')
                ->setTitle(Lang::get('global.new_resource'))
                ->setIcon('fa fa-edit')
                ->setId($model->getKey()),

            Tabs::make()
                ->setId('document')
                ->addTab('general', Lang::get('global.settings_general'), null, 'flex flex-wrap p-6')
                ->addSlot(
                    'general',
                    [
                        Template::make()
                            ->setClass('flex flex-wrap grow md:basis-2/3 xl:basis-9/12 md:pr-3')
                            ->setSlot([
                                Input::make(
                                    'pagetitle',
                                    Lang::get('global.resource_title'),
                                    '<b>[*pagetitle*]</b><br>' . Lang::get('global.resource_title_help'),
                                    'md:pr-2 md:basis-2/3'
                                )
                                    ->isRequired(),

                                Input::make(
                                    'alias',
                                    Lang::get('global.resource_alias'),
                                    '<b>[*alias*]</b><br>' . Lang::get('global.resource_alias_help'),
                                    'md:pl-2 md:basis-1/3'
                                ),

                                Input::make(
                                    'longtitle',
                                    Lang::get('global.long_title'),
                                    '<b>[*longtitle*]</b><br>' . Lang::get('global.resource_long_title_help')
                                ),

                                Textarea::make(
                                    'description',
                                    Lang::get('global.resource_description'),
                                    '<b>[*description*]</b><br>' . Lang::get('global.resource_description_help'),
                                    'md:pr-2 md:basis-1/2'
                                )
                                    ->setRows(3),

                                CodeEditor::make(
                                    'introtext',
                                    Lang::get('global.resource_summary'),
                                    '<b>[*introtext*]</b><br>' . Lang::get('global.resource_summary_help'),
                                    'md:pl-2 md:basis-1/2'
                                )
                                    ->setRows(3)
                                    ->setLanguage('html'),

                                CodeEditor::make(
                                    'content',
                                    Lang::get('global.resource_content'),
                                    '<b>[*content*]</b>'
                                )
                                    ->setRows(20)
                                    ->setLanguage('html'),
                            ]),

                        Template::make()
                            ->setClass('flex flex-wrap grow md:basis-1/3 xl:basis-3/12 md:pl-3')
                            ->setSlot([
                                Input::make(
                                    'parent',
                                    Lang::get('global.import_parent_resource'),
                                    '<b>[*parent*]</b><br>' . Lang::get('global.resource_parent_help')
                                )
                                    ->setInputClass('pr-8 appearance-select cursor-pointer')
                                    ->setValue(
                                        $model->parent ? $model->parents->pagetitle . ' (' . $model->parent . ')' : 0
                                    )
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
                                            'value' => $model->tpl->templatename ?? 'blank',
                                            'selected' => true,
                                        ],
                                    ]),

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
                                ),

                                DateTime::make(
                                    'pub_date',
                                    Lang::get('global.page_data_publishdate'),
                                    '<b>[*pub_date*]</b><br>' . Lang::get('global.page_data_publishdate_help')
                                ),

                                DateTime::make(
                                    'unpub_date',
                                    Lang::get('global.page_data_unpublishdate'),
                                    '<b>[*unpub_date*]</b><br>' . Lang::get('global.page_data_unpublishdate_help')
                                ),
                            ]),
                    ]
                )
                ->addTab('settings', Lang::get('global.settings_page_settings'), null, 'flex flex-wrap p-6')
                ->addSlot(
                    'settings',
                    [
                        Template::make()
                            ->setClass('flex flex-wrap grow md:basis-1/2 md:pr-3')
                            ->setSlot([
                                Select::make(
                                    'type',
                                    Lang::get('global.resource_type'),
                                    '<b>[*type*]</b><br>' . Lang::get('global.resource_type_message')
                                )
                                    ->isRequired()
                                    ->setData([
                                        [
                                            'key' => 'document',
                                            'value' => Lang::get('global.resource_type_webpage'),
                                        ],
                                        [
                                            'key' => 'reference',
                                            'value' => Lang::get('global.resource_type_weblink'),
                                        ],
                                    ]),

                                Select::make(
                                    'contentType',
                                    Lang::get('global.page_data_contentType'),
                                    '<b>[*contentType*]</b><br>' . Lang::get('global.page_data_contentType_help')
                                )
                                    ->isRequired()
                                    ->setData(
                                        array_map(fn($k) => [
                                            'key' => $k,
                                            'value' => $k,
                                        ], explode(',', Config::get('global.custom_contenttype')))
                                    ),

                                Select::make(
                                    'content_dispo',
                                    Lang::get('global.resource_opt_contentdispo'),
                                    '<b>[*content_dispo*]</b><br>' . Lang::get('global.resource_opt_contentdispo_help')
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
                            ->setClass('flex flex-wrap grow md:basis-1/2 md:pl-3')
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
                                    '<b>[*hide_from_tree*]</b><br>' . Lang::get('global.resource_opt_trackvisit_help')
                                )
                                    ->setCheckedValue(0, 1),

                                Checkbox::make(
                                    'alias_visible',
                                    Lang::get('global.resource_opt_alvisibled'),
                                    '<b>[*alias_visible*]</b><br>' . Lang::get('global.resource_opt_alvisibled_help')
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
                ->if(
                    $model->getTvs()->count(),
                    fn(Tabs $tabs) => $this->tabTvs(
                        $tabs,
                        $model->getTvs()
                    )
                )
                ->if(
                    Config::get('global.use_udperms'),
                    fn(Tabs $tabs) => $this->tabPermissions($tabs)
                ),
        ];
    }

    /**
     * @param SiteContent|null $model
     *
     * @return array
     */
    public function titleDefault(SiteContent $model = null): array
    {
        return [
            'title' => $model->pagetitle ?: Lang::get('global.new_resource'),
            'icon' => 'fa fa-edit',
        ];
    }

    /**
     * @param Tabs $tabs
     * @param Collection $tvs
     *
     * @return Tabs
     */
    protected function tabTvs(Tabs $tabs, Collection $tvs): Tabs
    {
        $tabs->addTab(
            'tvs',
            Lang::get('global.settings_templvars'),
            null,
            'flex flex-wrap'
        );

        $tvTabs = Tabs::make()
            ->setId('tvs')
            ->setClass('h-full')
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
                $tv['category_name'],
                null,
                'flex flex-wrap h-full p-6'
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
                        ->setModel('data.tvs.' . $tv['name'] . '.value')
                        ->setValue($tv['value'])
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
                        ->setModel('data.tvs.' . $tv['name'] . '.value')
                        ->setValue($tv['value'])
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

        return $tabs->addSlot(
            'tvs',
            $tvTabs
        );
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
            ->addTab(
                'permissions',
                Lang::get('global.access_permissions'),
                null,
                'flex flex-wrap p-6'
            )
            ->addSlot(
                'permissions',
                [
                    '<div class="pb-4">' . Lang::get('global.access_permissions_docs_message') . '</div>',

                    Checkbox::make()
                        ->setModel('data.is_document_group')
                        ->setLabel(Lang::get('global.all_doc_groups'))
                        ->setCheckedValue(true, false),

                    Checkbox::make()
                        ->setModel('data.document_groups')
                        ->setLabel(Lang::get('global.access_permissions_resource_groups'))
                        ->setData($groups),
                ]
            );
    }
}
