<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Section;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Template;

class DashboardLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            $this->getMessages(),
            $this->getWidgets(),
        ];
    }

    /**
     * @return Tabs
     */
    public function sidebar(): Tabs
    {
        return Tabs::make()
            ->setId('tree')
            ->setUid('TREE')
            ->setClass('h-full !bg-gray-800')
            ->isWatch()
            ->isLoadOnce()
            ->addTab(
                'documents',
                null,
                'fa fa-sitemap',
                '!bg-inherit',
                true,
                ['Document', 'Documents'],
                Lang::get('global.manage_documents'),
                [
                    'component' => 'EvoTree',
                    'attrs' => [
                        'id' => 'documents',
                        'route' => 'Document',
                        'routeList' => 'Documents',
                        'url' => '/document/tree?order=menuindex&dir=asc',
                        'aliases' => [
                            'hide_from_tree' => 'hideChildren',
                            'isfolder' => 'folder',
                            'hidemenu' => 'inhidden',
                            'children' => 'data',
                        ],
                        'icons' => [
                            'default' => 'far fa-file',
                            Config::get('global.unauthorized_page') => 'fa fa-lock text-rose-600',
                            Config::get('global.site_start') => 'fa fa-home text-blue-500',
                            Config::get('global.site_unavailable_page') => 'fa fa-ban text-amber-400',
                            Config::get('global.error_page') => 'fa fa-exclamation-triangle text-rose-600',
                            'reference' => 'fa fa-link',
                        ],
                        'templates' => [
                            'title' =>
                                Lang::get('global.pagetitle') . ': {pagetitle}' . PHP_EOL .
                                Lang::get('global.id') . ': {id}' . PHP_EOL .
                                Lang::get('global.resource_opt_menu_title') . ': {menutitle}' . PHP_EOL .
                                Lang::get('global.resource_opt_menu_index') . ': {menuindex}' . PHP_EOL .
                                Lang::get('global.alias') . ': {alias}' . PHP_EOL .
                                Lang::get('global.template') . ': {template}' . PHP_EOL .
                                Lang::get('global.resource_opt_richtext') . ': {richtext}' . PHP_EOL .
                                Lang::get('global.page_data_searchable') . ': {searchable}' . PHP_EOL .
                                Lang::get('global.page_data_cacheable') . ': {cacheable}' . PHP_EOL,
                        ],
                        'contextMenu' => [
                            'class' => 'text-base',
                            'actions' => [
                                [
                                    'title' => Lang::get('global.create_resource_here'),
                                    'icon' => 'fa fa-file',
                                    'to' => [
                                        'name' => 'Document',
                                        'params' => [
                                            'id' => 'new',
                                        ],
                                        'query' => [
                                            'type' => 'document',
                                        ],
                                    ],
                                ],
                                [
                                    'title' => Lang::get('global.create_weblink_here'),
                                    'icon' => 'fa fa-link',
                                    'to' => [
                                        'name' => 'Document',
                                        'params' => [
                                            'id' => 'new',
                                        ],
                                        'query' => [
                                            'type' => 'reference',
                                        ],
                                    ],
                                ],
                                [
                                    'title' => Lang::get('global.edit'),
                                    'icon' => 'fa fa-edit',
                                    'to' => [
                                        'name' => 'Document',
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
                                        'folder' => 0,
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
                                ],
                                [
                                    'title' => Lang::get('global.preview'),
                                    'icon' => 'fa fa-eye',
                                ],
                            ],
                        ],
                        'menu' => [
                            'actions' => [
                                [
                                    'icon' => 'fa fa-refresh',
                                    'click' => 'update',
                                    'loader' => true,
                                ],
                                [
                                    'icon' => 'fa fa-sort',
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
                            ],
                        ],
                        'settings' => [
                            'dir' => 'asc',
                            'order' => 'menuindex',
                            'keyTitle' => 'pagetitle',
                        ],
                    ],
                ]
            )
            ->if(
                Config::get('global.workspace_sidebar_templates', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'templates',
                    null,
                    'fa fa-newspaper',
                    '!bg-inherit',
                    ['edit_template'],
                    ['Template'],
                    Lang::get('global.templates'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'templates',
                            'route' => 'Template',
                            'url' => '/templates/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'templatename' => 'title',
                                'locked' => 'private',
                                'category' => 'parent',
                            ],
                            'icons' => [
                                'default' => 'fa fa-newspaper',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_tvs', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'tvs',
                    null,
                    'fa fa-list-alt',
                    '!bg-inherit',
                    ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                    ['Tv'],
                    Lang::get('global.tmplvars'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'tvs',
                            'route' => 'Tv',
                            'url' => '/tvs/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'locked' => 'private',
                                'category' => 'parent',
                            ],
                            'icons' => [
                                'default' => 'fa fa-list-alt',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_chunks', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'chunks',
                    null,
                    'fa fa-th-large',
                    '!bg-inherit',
                    ['edit_chunk'],
                    ['Chunk'],
                    Lang::get('global.htmlsnippets'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'chunks',
                            'route' => 'Chunk',
                            'url' => '/chunks/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'locked' => 'private',
                                'disabled' => 'deleted',
                            ],
                            'icons' => [
                                'default' => 'fa fa-th-large',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_snippets', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'snippets',
                    null,
                    'fa fa-code',
                    '!bg-inherit',
                    ['edit_snippet'],
                    ['Snippet'],
                    Lang::get('global.snippets'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'snippets',
                            'route' => 'Snippet',
                            'url' => '/snippets/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'locked' => 'private',
                                'disabled' => 'deleted',
                            ],
                            'icons' => [
                                'default' => 'fa fa-code',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_plugins', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'plugins',
                    null,
                    'fa fa-plug',
                    '!bg-inherit',
                    ['edit_plugin'],
                    ['Plugin'],
                    Lang::get('global.plugins'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'plugins',
                            'route' => 'Plugin',
                            'url' => '/plugins/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'locked' => 'private',
                                'disabled' => 'deleted',
                            ],
                            'icons' => [
                                'default' => 'fa fa-plug',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_modules', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'modules',
                    null,
                    'fa fa-cubes',
                    '!bg-inherit',
                    ['edit_module'],
                    ['Module'],
                    Lang::get('global.modules'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'modules',
                            'route' => 'Module',
                            'url' => '/modules/tree',
                            'category' => true,
                            'aliases' => [
                                'name' => 'title',
                                'locked' => 'private',
                                'disabled' => 'deleted',
                            ],
                            'icons' => [
                                'default' => 'fa fa-cubes',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_categories', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'categories',
                    null,
                    'fa fa-object-group',
                    '!bg-inherit',
                    ['category_manager'],
                    ['Category'],
                    Lang::get('global.category_management'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'categories',
                            'route' => 'Category',
                            'url' => '/categories/tree?order=category',
                            'category' => true,
                            'aliases' => [
                                'category' => 'title',
                            ],
                            'icons' => [
                                'default' => 'fa fa-object-group',
                            ],
                        ],
                    ]
                )
            )
            ->if(
                Config::get('global.workspace_sidebar_files', 1),
                fn(Tabs $tabs) => $tabs->addTab(
                    'files',
                    null,
                    'fa fa-folder-open',
                    '!bg-inherit',
                    ['file_manager'],
                    ['File'],
                    Lang::get('global.files_files'),
                    [
                        'component' => 'EvoTree',
                        'attrs' => [
                            'id' => 'treeFiles',
                            'route' => 'File',
                            'url' => '/file/tree',
                            'category' => true,
                            'aliases' => [],
                            'icons' => [
                                'default' => 'fa fa-ban',
                                'txt' => 'fa fa-code',
                                'css' => 'fa fa-code text-blue-500',
                                'less' => 'fa fa-code text-blue-500',
                                'cass' => 'fa fa-code text-blue-500',
                                'php' => 'fab fa-php text-purple-500',
                                'vue' => 'fab fa-vuejs text-emerald-500',
                                'ts' => 'fa fa-code text-green-500',
                                'mjs' => 'fa fa-code text-green-600',
                                'cjs' => 'fa fa-code text-green-600',
                                'js' => 'fa fa-code text-green-500',
                                'json' => 'fa fa-code text-green-500',
                                'xml' => 'fa fa-code text-green-500',
                                'yml' => 'fa fa-code',
                                'svg' => 'far fa-image',
                                'webp' => 'far fa-image',
                                'jpg' => 'far fa-image',
                                'jpeg' => 'far fa-image',
                                'png' => 'far fa-image',
                                'gif' => 'far fa-image',
                                'lock' => 'fa fa-lock text-rose-500',
                                'bat' => 'fa fa-file-code text-rose-800',
                                'md' => 'fa fa-code',
                                'artisan' => 'fa fa-code text-blue-500',
                                'htaccess' => 'fa fa-code',
                                'gitignore' => 'fab fa-git text-orange-700',
                                'gitattributes' => 'fab fa-git text-orange-700',
                                'env' => 'fa fa-code',
                                'editorconfig' => 'fa fa-code',
                                //'default' => 'far fa-file',
                                //                    'text/html' => 'far fa-file',
                                //                    'text/plain' => 'far fa-file',
                                //                    'text/x-php' => 'far fa-file',
                                //                    'text/x-java' => 'far fa-file',
                                //                    'text/x-js' => 'far fa-file',
                                //                    'text/xml' => 'far fa-file',
                                //                    'application/json' => 'far fa-file',
                            ],
                            'menu' => [
                                'actions' => [
                                    [
                                        'icon' => 'fa fa-refresh',
                                        'click' => 'update',
                                        'loader' => true,
                                    ],
                                    [
                                        'icon' => 'fa fa-ellipsis-vertical',
                                        'position' => 'right',
                                        'actions' => [
                                            [
                                                'key' => 'show',
                                                'value' => 'date',
                                                'title' => 'Показывать дату',
                                                'toggle' => true,
                                            ],
                                            [
                                                'key' => 'show',
                                                'value' => 'size',
                                                'title' => 'Показывать размер',
                                                'toggle' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'settings' => [
                                'show' => ['date'],
                            ],
                            'templates' => [
                                'title' =>
                                    Lang::get('global.createdon') . ': {_date}' . PHP_EOL .
                                    Lang::get('global.files_filesize') . ': {_size}' . PHP_EOL,
                            ],
                        ],
                    ]
                )
            );
    }

    /**
     * @return array
     */
    protected function getMessages(): array
    {
        return Template::make(
            'pt-6 px-6',
            [
                Template::make(
                    'block evo-alert-warning p-4 mb-3 rounded',
                    Lang::get('global.siteunavailable_message_default') .
                    ' ' . Lang::get('global.update_settings_from_language') .
                    '<a href="configuration" class="btn-sm btn-green ml-2">' . Lang::get('global.online') . '</a>'
                ),
                Template::make(
                    'block evo-alert-warning p-4 mb-0 rounded',
                    '<strong>' . Lang::get('global.configcheck_warning') . '</strong>' .
                    '<br>' . Lang::get('global.configcheck_installer') .
                    '<br><br><i>' . Lang::get('global.configcheck_what') . '</i>' .
                    '<br>' . Lang::get('global.configcheck_installer_msg')
                ),
            ]
        )
            ->toArray();
    }

    /**
     * @return array
     */
    protected function getWidgets(): array
    {
        return Template::make(
            'flex flex-wrap items-baseline pt-6 px-4'
        )
            ->putSlot(
                Template::make(
                    'grow w-full lg:basis-1/2 px-2',
                    Section::make(
                        'fa fa-home',
                        Lang::get('global.welcome_title'),
                        'lg:min-h-[15rem] content-baseline bg-white dark:bg-gray-700 hover:shadow-lg transition',
                        '<div class="data"><table>' .
                        '<tr><td class="w-52">' . Lang::get('global.yourinfo_username') .
                        '</td><td><strong>' .
                        Auth::user()->username . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_role') . '</td><td><strong>' .
                        Auth::user()->attributes->userRole->name . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_previous_login') . '</td><td><strong>' .
                        Auth::user()->attributes->lastlogin . '</strong></td></tr>' .
                        '<tr><td>' . Lang::get('global.yourinfo_total_logins') . '</td><td><strong>' .
                        Auth::user()->attributes->logincount . '</strong></td></tr>' .
                        '</table></div>'
                    )
                )
            )
            ->putSlot(
                Template::make(
                    'grow w-full lg:basis-1/2 px-2',
                    Section::make(
                        'fa fa-user',
                        Lang::get('global.onlineusers_title'),
                        'lg:min-h-[15rem] content-baseline bg-white dark:bg-gray-700 hover:shadow-lg transition',
                        [
                            '<div class="mb-4">' . Lang::get('global.onlineusers_message') . '<b>' .
                            date('H:i:s') . '</b>)</div>',
                            Panel::make()
                                ->setId('widgetUsers')
                                ->setClass('-mx-4 -mb-4 pb-4 grow')
                                ->setUrl('/users/active')
                                ->setHistory(false)
                                ->setRoute('User'),
                        ]
                    )
                )
            )
            ->if(
                Gate::check(['view_document']),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full px-2',
                        Section::make(
                            'fa fa-pencil',
                            Lang::get('global.activity_title'),
                            'hover:shadow-lg bg-white dark:bg-gray-700 transition overflow-hidden',
                            Panel::make()
                                ->setId('widgetDocuments')
                                ->setHistory(false)
                                ->setClass('-m-4 pb-4 grow')
                                ->setUrl(
                                    '/document?order=createdon&dir=desc&limit=10&columns=id,pagetitle,longtitle,createdon'
                                )
                                ->setRoute('Document')
                        )
                    )
                )
            )
            ->if(
                Config::get('global.rss_url_news'),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full lg:basis-1/2 px-2 pb-2',
                        Section::make(
                            'fa fa-rss',
                            Lang::get('global.modx_news'),
                            'overflow-hidden bg-white dark:bg-gray-700 hover:shadow-lg transition',
                            Panel::make()
                                ->setId('widgetNews')
                                ->setClass('-m-4 grow h-80 overflow-auto')
                                ->setUrl('/dashboard/news')
                        )
                    )
                )
            )
            ->if(
                Config::get('global.rss_url_security'),
                fn(Template $template) => $template->putSlot(
                    Template::make(
                        'grow w-full lg:basis-1/2 px-2 pb-2',
                        Section::make(
                            'fa fa-exclamation-triangle',
                            Lang::get('global.modx_security_notices'),
                            'overflow-hidden bg-white dark:bg-gray-700 hover:shadow-lg transition',
                            Panel::make()
                                ->setId('widgetNewsSecurity')
                                ->setClass('-m-4 grow h-80 overflow-auto')
                                ->setUrl('/dashboard/news-security')
                        )
                    )
                )
            )
            ->toArray();
    }
}
