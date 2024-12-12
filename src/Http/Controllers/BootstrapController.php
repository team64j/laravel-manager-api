<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\BootstrapRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Layouts\ChunkLayout;
use Team64j\LaravelManagerApi\Layouts\FilemanagerLayout;
use Team64j\LaravelManagerApi\Layouts\ModuleLayout;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Layouts\ResourceLayout;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Layouts\TemplateLayout;
use Team64j\LaravelManagerApi\Layouts\TvLayout;
use Team64j\LaravelManagerComponents\Tabs;

class BootstrapController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bootstrap",
     *     summary="Стартовые данные",
     *     tags={"Bootstrap"},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     *
     * @return ApiResource
     */
    public function init(): ApiResource
    {
        return ApiResource::make([
            'version' => config('global.settings_version'),
            'languages' => $this->getLanguages(),
            'siteName' => config('global.site_name'),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bootstrap",
     *     summary="Глобальные данные для формирования админ панели",
     *     tags={"Bootstrap"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     *
     * @param BootstrapRequest $request
     *
     * @return ApiResource
     */
    public function index(BootstrapRequest $request): ApiResource
    {
        $sidebar = $this->getSidebar();

        return ApiResource::make([
            'routes' => $this->getRoutes(),
            'assets' => $this->getAssets(),
            'config' => [
                'site_name' => config('global.site_name'),
                'site_status' => (bool) config('global.site_status'),
                'remember_last_tab' => (bool) config('global.remember_last_tab'),
                'datetime_format' => config('global.datetime_format'),
            ],
            'lang' => [
                'warning_not_saved' => __('global.warning_not_saved'),
                'dp_dayNames' => __('global.dp_dayNames'),
                'dp_monthNames' => __('global.dp_monthNames'),
                'dp_startDay' => __('global.dp_startDay'),
            ],
        ])
            ->layout([
                [
                    'component' => 'AppMainMenu',
                    'attrs' => [
                        'data' => $this->getMenu()[0]['data'],
                    ],
                    'slot' => 'top.left'
                ],
                [
                    'component' => 'AppMainMenu',
                    'attrs' => [
                        'data' => $this->getMenu()[1]['data'],
                    ],
                    'slot' => 'top.right'
                ],
//                [
//                    'component' => 'AppGlobalMenu',
//                    'attrs' => [
//                        'data' => $this->getMenu(),
//                    ],
//                    'slot' => 'top',
//                ],
                [
                    'component' => 'AppTabsNavigation',
                    'attrs' => $sidebar['attrs'],
                    'slot' => 'left.top',
                ],
                [
                    ...$sidebar,
                    'slot' => 'sidebar',
                ],
                [
                    'component' => 'AppGlobalTabs',
                    'slot' => 'main',
                ],
            ]);
    }

    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        return [
            [
                'path' => '/elements/:path',
                'meta' => [
                    'url' => '/:path?groupBy=category',
                    'group' => true,
                ],
            ],
            [
                'path' => '/permissions/:path',
                'meta' => [
                    'group' => true,
                ],
            ],
            [
                'path' => '/roles/:path',
                'meta' => [
                    'group' => true,
                ],
            ],
            [
                'path' => '/modules/exec/:id',
                'meta' => [
                    'icon' => 'fa fa-cube',
                    'isIframe' => true,
                    'title' => __('global.run_module'),
                ],
            ],
            [
                'path' => '/preview/:id',
                'meta' => [
                    'icon' => 'fa fa-desktop',
                    'isIframe' => true,
                ],
            ],
            [
                'path' => '/file/:id',
            ],
            [
                'path' => '/filemanager/:key?',
                'meta' => [
                    'group' => true,
                ],
            ],
            [
                'path' => '/:path(.*)',
            ],
            [
                'path' => '/:path(.*)/:id(\d+)',
            ],
//            [
//                'path' => '/:path(.*)/new',
//            ],
            [
                'path' => '/phpinfo',
                'meta' => [
                    'icon' => 'fab fa-php',
                    'title' => 'PHP Info',
                    'isIframe' => true,
                ],
            ],
        ];
        /*        return [
                    [
                        'path' => '/',
                        'redirect' => '/dashboard',
                        'meta' => [
                            'hidden' => true,
                        ],
                    ],
                    [
                        'path' => '/dashboard',
                        'name' => 'Dashboard',
                        'meta' => [
                            'fixed' => true,
                            'title' => null,
                            'icon' => 'fa fa-home',
                        ],
                    ],
                    [
                        'path' => '/resource/:id',
                        'name' => 'Resource',
                    ],
                    [
                        'path' => '/resources/:id',
                        'name' => 'Resources',
                    ],
                    [
                        'path' => '/elements/:element',
                        'name' => 'Elements',
                        'meta' => [
                            'url' => '/:element?groupBy=category',
                            'group' => true,
                        ],
                    ],
                    [
                        'path' => '/templates/:id',
                        'name' => 'Template',
                    ],
                    [
                        'path' => '/tvs/:id',
                        'name' => 'Tv',
                    ],
                    [
                        'path' => '/tvs/sort',
                        'name' => 'TvSort',
                    ],
                    [
                        'path' => '/chunks/:id',
                        'name' => 'Chunk',
                    ],
                    [
                        'path' => '/snippets/:id',
                        'name' => 'Snippet',
                    ],
                    [
                        'path' => '/plugins/:id',
                        'name' => 'Plugin',
                    ],
                    [
                        'path' => '/plugins/sort',
                        'name' => 'PluginSort',
                    ],
                    [
                        'path' => '/modules/:id',
                        'name' => 'Module',
                    ],
                    [
                        'path' => '/modules/exec/:id',
                        'name' => 'ModuleExec',
                        'meta' => [
                            'icon' => 'fa fa-cube',
                            'isIframe' => true,
                        ],
                    ],
                    [
                        'path' => '/categories/:id',
                        'name' => 'Category',
                    ],
                    [
                        'path' => '/categories/sort',
                        'name' => 'CategorySort',
                    ],
                    [
                        'path' => '/users/:id?',
                        'name' => 'User',
                    ],
                    [
                        'path' => '/roles/:element',
                        'name' => 'Roles',
                        'meta' => [
                            'group' => true,
                        ],
                    ],
                    [
                        'path' => '/roles/users/:id',
                        'name' => 'RoleUser',
                    ],
                    [
                        'path' => '/roles/categories/:id',
                        'name' => 'RoleCategory',
                    ],
                    [
                        'path' => '/roles/permissions/:id',
                        'name' => 'RolePermission',
                    ],
                    [
                        'path' => '/permissions/:element',
                        'name' => 'Permissions',
                        'meta' => [
                            'group' => true,
                        ],
                    ],
                    [
                        'path' => '/permissions/groups/:id',
                        'name' => 'PermissionGroup',
                    ],
                    [
                        'path' => '/permissions/relations/:id',
                        'name' => 'PermissionRelation',
                    ],
                    [
                        'path' => '/permissions/resources/:id',
                        'name' => 'PermissionResource',
                    ],
                    [
                        'path' => '/cache',
                        'name' => 'Cache',
                    ],
                    [
                        'path' => '/configuration',
                        'name' => 'Configuration',
                    ],
                    [
                        'path' => '/workspace',
                        'name' => 'Workspace',
                    ],
                    [
                        'path' => '/schedule',
                        'name' => 'Schedules',
                    ],
                    [
                        'path' => '/event-log',
                        'name' => 'EventLogs',
                    ],
                    [
                        'path' => '/event-log/:id',
                        'name' => 'EventLog',
                    ],
                    [
                        'path' => '/system-log',
                        'name' => 'SystemLog',
                    ],
                    [
                        'path' => '/system-info',
                        'name' => 'SystemInfo',
                    ],
                    [
                        'path' => '/phpinfo',
                        'name' => 'PhpInfo',
                        'meta' => [
                            'url' => '/system-info/phpinfo',
                            'icon' => 'fab fa-php',
                            'isIframe' => true,
                        ],
                    ],
                    [
                        'path' => '/help',
                        'name' => 'Help',
                    ],
                    [
                        'path' => '/password',
                        'name' => 'Password',
                    ],
                    [
                        'path' => '/files/:id?',
                        'name' => 'Files',
                        'meta' => [
                            'group' => true,
                        ],
                    ],
                    [
                        'path' => '/file/:id?',
                        'name' => 'File',
                    ],
                ];*/
    }

    /**
     * @return array
     */
    protected function getAssets(): array
    {
        $assets = [];

        $packageFolder = trim(
            str_replace([app()->basePath(), DIRECTORY_SEPARATOR], ['', '/'], dirname(__DIR__, 3)),
            '/'
        );

        $publicFolder = basename(app()->publicPath());

        $assets[] = [
            'rel' => 'manifest',
            'source' => str_replace(
                ['"' . $publicFolder, '"' . url('/'), '/..'],
                ['"', '"', url('/')],
                Vite::useBuildDirectory('../' . $packageFolder . '/dist')
                    ->withEntryPoints([
                        'resources/css/styles.css',
                    ])
                    ->toHtml()
            ),
        ];

        return $assets;
        //return array_merge($assets, Arr::flatten(Event::until('OnManagerMainFrameHeaderHTMLBlock'), 1) ?? []);
    }

    /**
     * @param bool $edit
     *
     * @return array
     */
    public function getMenu(bool $edit = false): array
    {
        if (!$edit && config()->has('global.workspace_topmenu_data') &&
            config('global.workspace_topmenu_data') != '[]'
        ) {
            $data = config('global.workspace_topmenu_data');
        } else {
            $data = json_encode([
                [
                    'key' => 'primary',
                    'data' => [
//                        [
//                            'key' => 'sidebarShow',
//                            'values' => [
//                                [
//                                    'value' => true,
//                                    'icon' => 'fa fa-bars',
//                                ],
//                                [
//                                    'value' => false,
//                                    'icon' => 'fa fa-ellipsis-vertical',
//                                ],
//                            ],
//                        ],
                        [
                            'key' => 'dashboard',
                            'icon' => config('global.login_logo')
                                ?: 'https://avatars.githubusercontent.com/u/46722965?s=64&v=4',
                            'class' => 'line-height-1',
                            'to' => [
                                'path' => '/',
                            ],
                            'permissions' => ['home'],
                        ],
                        [
                            'key' => 'elements',
                            'name' => '[%elements%]',
                            'icon' => 'fa fa-th',
                            'data' => [
                                [
                                    'key' => 'templates',
                                    'name' => '[%templates%]',
                                    'icon' => 'fa fa-newspaper',
                                    'to' => [
                                        'path' => '/elements/templates',
                                    ],
                                    'url' => '/templates/list',
                                    'permissions' => ['new_template', 'edit_template'],
                                ],
                                [
                                    'key' => 'tvs',
                                    'name' => '[%tmplvars%]',
                                    'icon' => 'fa fa-list-alt',
                                    'to' => [
                                        'path' => '/elements/tvs',
                                    ],
                                    'url' => '/tvs/list',
                                    'permissions' => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                                ],
                                [
                                    'key' => 'chunks',
                                    'name' => '[%htmlsnippets%]',
                                    'icon' => 'fa fa-th-large',
                                    'to' => [
                                        'path' => '/elements/chunks',
                                    ],
                                    'url' => '/chunks/list',
                                    'permissions' => ['edit_chunk'],
                                ],
                                [
                                    'key' => 'snippets',
                                    'name' => '[%snippets%]',
                                    'icon' => 'fa fa-code',
                                    'to' => [
                                        'path' => '/elements/snippets',
                                    ],
                                    'url' => '/snippets/list',
                                    'permissions' => ['edit_snippet'],
                                ],
                                [
                                    'key' => 'plugins',
                                    'name' => '[%plugins%]',
                                    'icon' => 'fa fa-plug',
                                    'to' => [
                                        'path' => '/elements/plugins',
                                    ],
                                    'url' => '/plugins/list',
                                    'permissions' => ['edit_plugin'],
                                ],
                                [
                                    'key' => 'modules',
                                    'name' => '[%modules%]',
                                    'icon' => 'fa fa-cubes',
                                    'to' => [
                                        'path' => '/elements/modules',
                                    ],
                                    'url' => '/modules/list',
                                    'permissions' => ['edit_module'],
                                ],
                                [
                                    'key' => 'categories',
                                    'name' => '[%category_management%]',
                                    'icon' => 'fa fa-object-group',
                                    'to' => [
                                        'path' => '/elements/categories',
                                    ],
                                    'url' => '/categories/list',
                                    'permissions' => ['category_manager'],
                                ],
                                [
                                    'key' => 'filemanager',
                                    'name' => '[%settings_misc%]',
                                    'icon' => 'far fa-folder-open',
                                    'to' => [
                                        'path' => '/filemanager',
                                    ],
                                    'permissions' => ['file_manager'],
                                ],
                            ],
                        ],
                        [
                            'key' => 'modules',
                            'name' => '[%modules%]',
                            'icon' => 'fa fa-cubes',
                            'url' => '/modules/exec',
                            'permissions' => ['exec_module'],
                        ],
                        [
                            'key' => 'users',
                            'name' => '[%users%]',
                            'icon' => 'fa fa-users',
                            'data' => [
                                [
                                    'key' => 'managers',
                                    'name' => '[%users%]',
                                    'icon' => 'fa fa-user-circle',
                                    'to' => [
                                        'path' => '/users',
                                    ],
                                    'url' => '/users/list',
                                    'permissions' => ['edit_user'],
                                ],
                                [
                                    'key' => 'roles',
                                    'name' => '[%role_management_title%]',
                                    'icon' => 'fa fa-legal',
                                    'to' => [
                                        'path' => '/roles/users',
                                    ],
                                    'permissions' => ['edit_role'],
                                ],
                                [
                                    'key' => 'permissions',
                                    'name' => '[%web_permissions%]',
                                    'icon' => 'fa fa-male',
                                    'to' => [
                                        'path' => '/permissions/groups',
                                    ],
                                    'permissions' => ['access_permissions'],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tools',
                            'name' => '[%tools%]',
                            'icon' => 'fa fa-wrench',
                            'data' => [
                                [
                                    'key' => 'cache',
                                    'name' => '[%refresh_site%]',
                                    'icon' => 'fa fa-recycle',
                                    'to' => [
                                        'path' => '/cache',
                                    ],
                                    'permissions' => ['empty_cache'],
                                ],
                            ],
                        ],
                    ],
                ],

                [
                    'key' => 'secondary',
                    'data' => [
                        [
                            'key' => 'searchShow',
                            'icon' => 'fa fa-search',
                            'values' => [
                                [
                                    'value' => true,
                                ],
                                [
                                    'value' => false,
                                ],
                            ],
                        ],
                        [
                            'key' => 'siteStatus',
                            'icon' => 'fa fa-desktop',
                            'value' => config('global.site_status'),
                            'values' => [
                                [
                                    'icon' => 'fa fa-desktop relative',
                                    'title' => '[%online%]',
                                    'value' => '1'
                                ],
                                [
                                    'icon' => 'fa fa-triangle-exclamation text-amber-400',
                                    'title' => config('global.site_unavailable_message'),
                                    'value' => '0'
                                ],
                            ],
                            'href' => url('/'),
                            'target' => '_blank',
                        ],
                        [
                            'key' => 'account',
                            'icon' => 'far fa-user-circle',
                            'image' => '[+user.photo+]',
                            'name' => '[+user.username+]',
                            'data' => [
                                [
                                    'key' => 'dark',
                                    'values' => [
                                        [
                                            'icon' => 'fa fa-sun fa-fw',
                                            'name' => 'Light theme',
                                            'value' => true,
                                        ],
                                        [
                                            'icon' => 'fa fa-moon fa-fw',
                                            'name' => 'Dark theme',
                                            'value' => false,
                                        ],
                                    ],
                                ],
                                [
                                    'key' => 'workspace',
                                    'icon' => 'fa fa-eye',
                                    'name' => '[%settings_ui%]',
                                    'to' => [
                                        'path' => '/workspace',
                                    ],
                                    'permissions' => ['settings'],
                                ],
                                [
                                    'key' => 'password',
                                    'icon' => 'fa fa-lock',
                                    'name' => '[%change_password%]',
                                    'to' => [
                                        'path' => '/password',
                                    ],
                                    'permissions' => ['change_password'],
                                ],
                                [
                                    'key' => 'logout',
                                    'icon' => 'fa fa-sign-out',
                                    'name' => '[%logout%]',
                                    'to' => [
                                        'path' => '/logout',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'settings',
                            'icon' => 'fa fa-cogs',
                            'data' => [
                                [
                                    'key' => 'edit_settings',
                                    'icon' => 'fa fa-sliders',
                                    'name' => '[%edit_settings%]',
                                    'to' => [
                                        'path' => '/configuration',
                                    ],
                                    'permissions' => ['settings'],
                                ],
                                [
                                    'key' => 'site_schedule',
                                    'icon' => 'far fa-calendar',
                                    'name' => '[%site_schedule%]',
                                    'to' => [
                                        'path' => '/schedule',
                                    ],
                                    'permissions' => ['view_eventlog'],
                                ],
                                [
                                    'key' => 'eventlog_viewer',
                                    'icon' => 'fa fa-exclamation-triangle',
                                    'name' => '[%eventlog_viewer%]',
                                    'to' => [
                                        'path' => '/event-log',
                                    ],
                                    'permissions' => ['view_eventlog'],
                                ],
                                [
                                    'key' => 'view_logging',
                                    'icon' => 'fa fa-user-secret',
                                    'name' => '[%view_logging%]',
                                    'to' => [
                                        'path' => '/system-log',
                                    ],
                                    'permissions' => ['logs'],
                                ],
                                [
                                    'key' => 'view_sysinfo',
                                    'icon' => 'fa fa-info',
                                    'name' => '[%view_sysinfo%]',
                                    'to' => [
                                        'path' => '/system-info',
                                    ],
                                ],
                                [
                                    'key' => 'help',
                                    'icon' => 'far fa-question-circle',
                                    'name' => '[%help%]',
                                    'to' => [
                                        'path' => '/help',
                                    ],
                                    'permissions' => ['help'],
                                ],
                                [
                                    'key' => 'settings_version',
                                    'name' => 'Evolution CE [(settings_version)]',
                                    'class' => 'text-center text-sm disabled',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        if ($edit) {
            return json_decode($data, true);
        }

        $data = json_decode($this->replaceVariables($data), true);

        if (auth()->user()->isAdmin()) {
            return $data;
        }

        return $this->checkMenuPermissions($data);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function replaceVariables(string $data): string
    {
        $data = $this->replaceUserVariables($data);
        $data = $this->replaceConfigVariables($data);
        $data = $this->replaceLangVariables($data);

        return $this->replaceUrlVariables($data);
    }

    /**
     * @param string $item
     *
     * @return string
     */
    protected function replaceConfigVariables(string $item): string
    {
        preg_match_all('!\[\((.*)\)]!U', $item, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $item = str_replace(
                    '[(' . $match . ')]',
                    config('global.' . $match, ''),
                    $item
                );
            }
        }

        return $item;
    }

    /**
     * @param string $item
     *
     * @return string
     */
    protected function replaceLangVariables(string $item): string
    {
        preg_match_all('!\[%(.*)%]!U', $item, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $item = str_replace(
                    '[%' . $match . '%]',
                    trans()->has('global.' . $match) ? __('global.' . $match) : '',
                    $item
                );
            }
        }

        return $item;
    }

    /**
     * @param string $item
     *
     * @return string
     */
    protected function replaceUrlVariables(string $item): string
    {
        preg_match_all('!\[~(.*)~]!U', $item, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $item = str_replace(
                    '[~' . $match . '~]',
                    route($match),
                    $item
                );
            }
        }

        return $item;
    }

    /**
     * @param string $item
     *
     * @return string
     */
    protected function replaceUserVariables(string $item): string
    {
        preg_match_all('!\[\+user\.(.*)\+]!U', $item, $matches);

        if (!empty($matches[1])) {
            $model = auth()->user();

            $user = [
                'name' => $model->username,
                'username' => $model->username,
                'photo' => $model->attributes->photo,
            ];

            foreach ($matches[1] as $match) {
                $item = str_replace(
                    '[+user.' . $match . '+]',
                    $user[$match] ?? '',
                    $item
                );
            }
        }

        return $item;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function checkMenuPermissions(array $data): array
    {
        foreach ($data as $k => &$item) {
            if (!empty($item['permissions'])) {
                if (!auth()->user()->can($item['permissions'])) {
                    unset($data[$k]);
                }
                unset($item['permissions']);
            } elseif (is_array($item)) {
                $item = $this->checkMenuPermissions($item);
            }

            if (isset($item['data'])) {
                if ($item['data']) {
                    $item['data'] = array_values($item['data']);
                } elseif (empty($item['url'])) {
                    unset($data[$k]);
                }
            }
        }

        return $data;
    }

    /**
     * @OA\Get(
     *     path="/bootstrap/select-pages",
     *     summary="Список доступных страниц-компонентов",
     *     tags={"Bootstrap"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     *
     * @return array
     */
    public function selectPages(): array
    {
        $data = [
            '' => '',
            '{"name":"Dashboard"}' => __('global.home'),
            '{"name":"Elements"}' => __('global.elements'),
            '{"name":"Elements","params":{"element":"templates"}}' => __('global.elements') . ' - ' .
                __('global.templates'),
            '{"name":"Elements","params":{"element":"tvs"}}' => __('global.elements') . ' - ' .
                __('global.tmplvars'),
            '{"name":"Elements","params":{"element":"chunks"}}' => __('global.elements') . ' - ' .
                __('global.htmlsnippets'),
            '{"name":"Elements","params":{"element":"snippets"}}' => __('global.elements') . ' - ' .
                __('global.snippets'),
            '{"name":"Elements","params":{"element":"plugins"}}' => __('global.elements') . ' - ' .
                __('global.plugins'),
            '{"name":"Elements","params":{"element":"modules"}}' => __('global.elements') . ' - ' .
                __('global.modules'),
            '{"name":"Elements","params":{"element":"categories"}}' => __('global.elements') . ' - ' .
                __('global.category_management'),
            '{"name":"ModuleExec"}' => __('global.role_run_module'),
            '{"name":"User"}' => __('global.users'),
            '{"name":"Roles","params":{"element":"users"}}' => __('global.role_role_management') . ' - ' .
                __('global.role_role_management'),
            '{"name":"Roles","params":{"element":"categories"}}' => __('global.role_role_management') . ' - ' .
                __('global.category_management'),
            '{"name":"Roles","params":{"element":"permissions"}}' => __('global.role_role_management') . ' - ' .
                __('global.manage_permission'),
            '{"name":"Permissions","params":{"element":"groups"}}' => __('global.manage_permission') . ' - ' .
                __('global.access_permissions_user_groups'),
            '{"name":"Permissions","params":{"element":"relations"}}' => __('global.manage_permission') . ' - ' .
                __('global.access_permissions_resource_groups'),
            '{"name":"Permissions","params":{"element":"resources"}}' => __('global.manage_permission') . ' - ' .
                __('global.access_permissions_links'),
            '{"name":"Configuration"}' => __('global.settings_title'),
            '{"name":"Workspace"}' => __('global.settings_ui'),
            '{"name":"Schedules"}' => __('global.site_schedule'),
            '{"name":"EventLogs"}' => __('global.eventlog_viewer'),
            '{"name":"EventLog"}' => __('global.eventlog'),
            '{"name":"SystemLog"}' => __('global.mgrlog_view'),
            '{"name":"SystemInfo"}' => __('global.view_sysinfo'),
            '{"name":"PhpInfo"}' => 'PHP Version',
            '{"name":"Help"}' => __('global.help'),
            '{"name":"Files"}' => __('global.manage_files'),
            '{"name":"File"}' => __('global.files_management'),
            '{"name":"Cache"}' => __('global.resource_opt_emptycache'),
            '{"name":"Password"}' => __('global.change_password'),
            '{"name":"Logout"}' => __('global.logout'),
        ];

        return [
            'data' => array_map(fn($value, $key) => [
                'key' => $key,
                'value' => $value,
            ], $data, array_keys($data)),
        ];
    }

    /**
     * @param bool $edit
     *
     * @return array
     */
    public function getSidebar(bool $edit = false): array
    {
        $tabs = Tabs::make()
            ->setId('tree')
            ->setUid('TREE')
            ->setClass('h-full')
            ->isVertical()
            ->isHideable()
            ->setNavigation(false)
            ->isSmallTabs()
            ->isLoadOnce();

        if (!$edit && config()->has('global.workspace_tree_data') && config('global.workspace_tree_data') != '[]') {
            $data = json_decode(config('global.workspace_tree_data'), true);
        } else {
            $data = [
                [
                    'lang' => 'manage_documents',
                    'class' => ResourceLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'templates',
                    'class' => TemplateLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'tmplvars',
                    'class' => TvLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'htmlsnippets',
                    'class' => ChunkLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'snippets',
                    'class' => SnippetLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'plugins',
                    'class' => PluginLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'modules',
                    'class' => ModuleLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'category_management',
                    'class' => CategoryLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
                [
                    'lang' => 'files_files',
                    'class' => FilemanagerLayout::class . '@tree',
                    'enabled' => true,
                    'custom' => false,
                ],
            ];
        }

        if ($edit) {
            return $data;
        }

        foreach ($data as $v) {
            $class = explode('@', (string) $v['class']);

            if (!$v['enabled'] || !$v['class'] || count($class) < 2 ||
                (class_exists($class[0]) && !method_exists(...$class))
            ) {
                continue;
            }

            $tab = app()->call($v['class']);

            if ($tab) {
                $tabs->addTab(...$tab);
            }
        }

        return $tabs->toArray();
    }

    /**
     * get languages
     *
     * @return array
     */
    protected function getLanguages(): array
    {
        $data = [];

        $languages = [
            'be' => 'Беларуская мова',
            'bg' => 'Български език',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'de' => 'Deutsch',
            'en' => 'English',
            'es' => 'Español',
            'he' => 'עברית ʿ',
            'ja' => '日本語',
            'fa' => 'فارسی',
            'fi' => 'Suomi',
            'fr' => 'Français',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'nn' => 'Nynorsk',
            'pl' => 'Język polski',
            'pt' => 'Português',
            'ru' => 'Русский',
            'sv' => 'Svenska',
            'uk' => 'Українська мова',
            'zh' => '中文',
        ];

        foreach (glob(dirname(__DIR__, 3) . '/lang/*/global.php') as $file) {
            $lang = require $file;

            if (isset($modx_lang_attribute) && !empty($languages[$modx_lang_attribute])) {
                $data[] = [
                    'key' => $modx_lang_attribute,
                    'value' => $languages[$modx_lang_attribute],
                    'user' => $lang['username'] ?? null,
                    'password' => $lang['password'] ?? null,
                    'remember' => $lang['remember_username'] ?? null,
                    'login' => $lang['login_button'] ?? null,
                ];
            }
        }

        return $data;
    }
}
