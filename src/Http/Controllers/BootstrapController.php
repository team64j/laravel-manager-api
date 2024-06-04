<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Vite;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Http\Requests\BootstrapRequest;
use Team64j\LaravelManagerApi\Http\Resources\BootstrapResource;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Layouts\ChunkLayout;
use Team64j\LaravelManagerApi\Layouts\FilesLayout;
use Team64j\LaravelManagerApi\Layouts\ModuleLayout;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Layouts\ResourceLayout;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Layouts\TemplateLayout;
use Team64j\LaravelManagerApi\Layouts\TvLayout;

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
     * @return BootstrapResource
     */
    public function init(): BootstrapResource
    {
        return BootstrapResource::make([])
            ->additional([
                'meta' => [
                    'version' => Config::get('global.settings_version'),
                    'languages' => $this->getLanguages(),
                    'siteName' => Config::get('global.site_name'),
                ],
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
     * @return BootstrapResource
     */
    public function index(BootstrapRequest $request): BootstrapResource
    {
        return BootstrapResource::make([
            'routes' => $this->getRoutes(),
            'assets' => $this->getAssets(),
            'config' => [
                'site_name' => Config::get('global.site_name'),
                'site_status' => (bool) Config::get('global.site_status'),
                'remember_last_tab' => (bool) Config::get('global.remember_last_tab'),
                'datetime_format' => Config::get('global.datetime_format'),
            ],
            'lang' => [
                'warning_not_saved' => Lang::get('global.warning_not_saved'),
                'dp_dayNames' => Lang::get('global.dp_dayNames'),
                'dp_monthNames' => Lang::get('global.dp_monthNames'),
                'dp_startDay' => Lang::get('global.dp_startDay'),
            ],
        ])
            ->additional([
                'layout' => [
                    'menu' => $this->getMenu(),
                    'sidebar' => $this->getSidebar(),
                    'main' => [],
                ]
            ]);
    }

    /**
     * @return array
     */
    protected function getRoutes(): array
    {
        return [
            [
                'path' => '/elements/:element',
                'meta' => [
                    'url' => '/:element?groupBy=category',
                    'group' => true,
                ],
            ],
            [
                'path' => '/modules/exec/:id',
                'meta' => [
                    'icon' => 'fa fa-cube',
                    'isIframe' => true,
                    'title' => Lang::get('global.run_module'),
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
                'path' => '/:path(.*)',
            ],
            [
                'path' => '/:path(.*)/:id(\\d+)',
            ],
            [
                'path' => '/:path(.*)/new',
            ],
            [
                'path' => '/:path(.*)/:element',
                'meta' => [
                    'group' => true,
                ],
            ],
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
        if (!$edit && Config::has('global.workspace_topmenu_data') &&
            Config::get('global.workspace_topmenu_data') != '[]'
        ) {
            $data = Config::get('global.workspace_topmenu_data');
        } else {
            $data = json_encode([
                [
                    'key' => 'primary',
                    'data' => [
                        [
                            'key' => 'sidebarShow',
                            'values' => [
                                [
                                    'value' => true,
                                    'icon' => 'fa fa-bars',
                                ],
                                [
                                    'value' => false,
                                    'icon' => 'fa fa-ellipsis-vertical',
                                ],
                            ],
                        ],
                        [
                            'key' => 'dashboard',
                            'icon' => Config::get('global.login_logo')
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
                                    'key' => 'files',
                                    'name' => '[%manage_files%]',
                                    'icon' => 'far fa-folder-open',
                                    'to' => [
                                        'path' => '/files',
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
                            'value' => Config::get('global.site_status'),
                            'values' => [
                                [
                                    'icon' => 'fa fa-desktop relative',
                                    'value' => '1'
                                ],
                                [
                                    'icon' => 'fa fa-triangle-exclamation text-amber-400',
                                    'title' => '[(site_unavailable_message)]',
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

        if (Auth::user()->isAdmin()) {
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
        $data = $this->replaceUrlVariables($data);

        return $data;
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
                    Config::get('global.' . $match, ''),
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
                    Lang::has('global.' . $match) ? Lang::get('global.' . $match) : '',
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
            $user = [
                'name' => Auth::user()->username,
                'username' => Auth::user()->username,
                'photo' => Auth::user()->attributes->photo,
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
                if (!Gate::check($item['permissions'])) {
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
            '{"name":"Dashboard"}' => Lang::get('global.home'),
            '{"name":"Elements"}' => Lang::get('global.elements'),
            '{"name":"Elements","params":{"element":"templates"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.templates'),
            '{"name":"Elements","params":{"element":"tvs"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.tmplvars'),
            '{"name":"Elements","params":{"element":"chunks"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.htmlsnippets'),
            '{"name":"Elements","params":{"element":"snippets"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.snippets'),
            '{"name":"Elements","params":{"element":"plugins"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.plugins'),
            '{"name":"Elements","params":{"element":"modules"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.modules'),
            '{"name":"Elements","params":{"element":"categories"}}' => Lang::get('global.elements') . ' - ' .
                Lang::get('global.category_management'),
            '{"name":"ModuleExec"}' => Lang::get('global.role_run_module'),
            '{"name":"User"}' => Lang::get('global.users'),
            '{"name":"Roles","params":{"element":"users"}}' => Lang::get('global.role_role_management') . ' - ' .
                Lang::get('global.role_role_management'),
            '{"name":"Roles","params":{"element":"categories"}}' => Lang::get('global.role_role_management') . ' - ' .
                Lang::get('global.category_management'),
            '{"name":"Roles","params":{"element":"permissions"}}' => Lang::get('global.role_role_management') . ' - ' .
                Lang::get('global.manage_permission'),
            '{"name":"Permissions","params":{"element":"groups"}}' => Lang::get('global.manage_permission') . ' - ' .
                Lang::get('global.access_permissions_user_groups'),
            '{"name":"Permissions","params":{"element":"relations"}}' => Lang::get('global.manage_permission') . ' - ' .
                Lang::get('global.access_permissions_resource_groups'),
            '{"name":"Permissions","params":{"element":"resources"}}' => Lang::get('global.manage_permission') . ' - ' .
                Lang::get('global.access_permissions_links'),
            '{"name":"Configuration"}' => Lang::get('global.settings_title'),
            '{"name":"Workspace"}' => Lang::get('global.settings_ui'),
            '{"name":"Schedules"}' => Lang::get('global.site_schedule'),
            '{"name":"EventLogs"}' => Lang::get('global.eventlog_viewer'),
            '{"name":"EventLog"}' => Lang::get('global.eventlog'),
            '{"name":"SystemLog"}' => Lang::get('global.mgrlog_view'),
            '{"name":"SystemInfo"}' => Lang::get('global.view_sysinfo'),
            '{"name":"PhpInfo"}' => 'PHP Version',
            '{"name":"Help"}' => Lang::get('global.help'),
            '{"name":"Files"}' => Lang::get('global.manage_files'),
            '{"name":"File"}' => Lang::get('global.files_management'),
            '{"name":"Cache"}' => Lang::get('global.resource_opt_emptycache'),
            '{"name":"Password"}' => Lang::get('global.change_password'),
            '{"name":"Logout"}' => Lang::get('global.logout'),
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
            ->setNavigation(false)
            ->isSmallTabs()
            ->isLoadOnce();

        if (!$edit && Config::has('global.workspace_tree_data') && Config::get('global.workspace_tree_data') != '[]') {
            $data = json_decode(Config::get('global.workspace_tree_data'), true);
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
                    'class' => FilesLayout::class . '@tree',
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

            $tab = App::call($v['class']);

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
