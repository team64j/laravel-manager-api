<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Team64j\LaravelManagerApi\Http\Requests\BootstrapRequest;
use Team64j\LaravelManagerApi\Http\Resources\BootstrapResource;
use Team64j\LaravelManagerApi\Models\UserAttribute;

class BootstrapController extends Controller
{
    protected string $route = 'bootstrap';

    protected array $routes = [
        [
            'method' => 'get',
            'uri' => 'select-pages',
            'action' => [self::class, 'selectPages'],
        ],
    ];

    protected array $routeOptions = [
        'only' => ['index']
    ];

    /**
     * @param BootstrapRequest $request
     *
     * @return BootstrapResource
     */
    public function index(BootstrapRequest $request): BootstrapResource
    {
        /** @var UserAttribute $userAttributes */
        $userAttributes = Auth::user()->attributes;

        return new BootstrapResource([
            'config' => [
                'APP_NAME' => Env::get('APP_NAME'),
                'site_id' => Config::get('global.site_id'),
                'site_name' => Config::get('global.site_name'),
                'site_url' => URL::to('/', [], Config::get('global.server_protocol') == 'https'),
                'site_start' => (int) Config::get('global.site_start'),
                'site_status' => (int) Config::get('global.site_status'),
                'error_page' => (int) Config::get('global.error_page'),
                'unauthorized_page' => (int) Config::get('global.unauthorized_page'),
                'site_unavailable_page' => (int) Config::get('global.site_unavailable_page'),
                'remember_last_tab' => (bool) Config::get('global.remember_last_tab'),
                'datetime_format' => Config::get('global.datetime_format'),
                'rb_base_url' => Config::get('global.rb_base_url'),
                'session_timeout' => Config::get('global.session_timeout'),
            ],
            'user' => [
                'username' => Auth::user()->username,
                'role' => $userAttributes->role,
                'permissions' => $userAttributes->rolePermissions->pluck('permission'),
            ],
            'lexicon' => Lang::get('global'),
            'menu' => $this->getMenu(),
            'css' => file_get_contents(__DIR__ . '/../../../dist/styles.css'),
        ]);
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
                            'key' => 'toggleSidebar',
                            'icon' => 'fa fa-bars',
                            'click' => 'toggleSidebar',
                        ],
                        [
                            'key' => 'dashboard',
                            'icon' => 'fa-logo',
                            'class' => 'line-height-1',
                            'click' => [
                                'name' => 'Dashboard',
                            ],
                            'permissions' => ['home'],
                        ],
                        [
                            'key' => 'elements',
                            'name' => '[%elements%]',
                            'icon' => 'fa fa-th md:hidden',
                            'data' => [
                                [
                                    'key' => 'templates',
                                    'name' => '[%templates%]',
                                    'icon' => 'fa fa-newspaper',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'templates',
                                        ],
                                    ],
                                    'url' => 'api/templates/list',
                                    'permissions' => ['new_template', 'edit_template'],
                                ],
                                [
                                    'key' => 'tvs',
                                    'name' => '[%tmplvars%]',
                                    'icon' => 'fa fa-list-alt',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'tvs',
                                        ],
                                    ],
                                    'url' => 'api/tvs/list',
                                    'permissions' => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
                                ],
                                [
                                    'key' => 'chunks',
                                    'name' => '[%htmlsnippets%]',
                                    'icon' => 'fa fa-th-large',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'chunks',
                                        ],
                                    ],
                                    'url' => 'api/chunks/list',
                                    'permissions' => ['edit_chunk'],
                                ],
                                [
                                    'key' => 'snippets',
                                    'name' => '[%snippets%]',
                                    'icon' => 'fa fa-code',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'snippets',
                                        ],
                                    ],
                                    'url' => 'api/snippets/list',
                                    'permissions' => ['edit_snippet'],
                                ],
                                [
                                    'key' => 'plugins',
                                    'name' => '[%plugins%]',
                                    'icon' => 'fa fa-plug',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'plugins',
                                        ],
                                    ],
                                    'url' => 'api/plugins/list',
                                    'permissions' => ['edit_plugin'],
                                ],
                                [
                                    'key' => 'modules',
                                    'name' => '[%modules%]',
                                    'icon' => 'fa fa-cubes',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'modules',
                                        ],
                                    ],
                                    'url' => 'api/modules/list',
                                    'permissions' => ['edit_module'],
                                ],
                                [
                                    'key' => 'categories',
                                    'name' => '[%category_management%]',
                                    'icon' => 'fa fa-object-group',
                                    'click' => [
                                        'name' => 'Elements',
                                        'params' => [
                                            'element' => 'categories',
                                        ],
                                    ],
                                    'permissions' => ['category_manager'],
                                ],
                                [
                                    'key' => 'files',
                                    'name' => '[%manage_files%]',
                                    'icon' => 'far fa-folder-open',
                                    'click' => [
                                        'name' => 'Files',
                                    ],
                                    'permissions' => ['file_manager'],
                                ],
                            ],
                        ],
                        [
                            'key' => 'modules',
                            'name' => '[%modules%]',
                            'icon' => 'fa fa-cubes md:hidden',
                            'url' => 'api/modules/exec',
                            'permissions' => ['exec_module'],
                        ],
                        [
                            'key' => 'users',
                            'name' => '[%users%]',
                            'icon' => 'fa fa-users md:hidden',
                            'data' => [
                                [
                                    'key' => 'managers',
                                    'name' => '[%users%]',
                                    'icon' => 'fa fa-user-circle',
                                    'click' => [
                                        'name' => 'User',
                                    ],
                                    'url' => 'api/users/list',
                                    'permissions' => ['edit_user'],
                                ],
                                [
                                    'key' => 'roles',
                                    'name' => '[%role_management_title%]',
                                    'icon' => 'fa fa-legal',
                                    'click' => [
                                        'name' => 'Roles',
                                        'params' => [
                                            'element' => 'users',
                                        ],
                                    ],
                                    'permissions' => ['edit_role'],
                                ],
                                [
                                    'key' => 'permissions',
                                    'name' => '[%web_permissions%]',
                                    'icon' => 'fa fa-male',
                                    'click' => [
                                        'name' => 'Permissions',
                                        'params' => [
                                            'element' => 'groups',
                                        ],
                                    ],
                                    'permissions' => ['access_permissions'],
                                ],
                            ],
                        ],
                        [
                            'key' => 'tools',
                            'name' => '[%tools%]',
                            'icon' => 'fa fa-wrench md:hidden',
                            'data' => [
                                [
                                    'key' => 'cache',
                                    'name' => '[%refresh_site%]',
                                    'icon' => 'fa fa-recycle',
                                    'click' => [
                                        'name' => 'Cache',
                                    ],
                                    'permissions' => ['empty_cache'],
                                ],
                                //                            [
                                //                                'key' => 'search',
                                //                                'name' => '[%search%]',
                                //                                'icon' => 'fa fa-search',
                                //                                'click' => [
                                //                                    'name' => 'Search',
                                //                                ],
                                //                            ],
                            ],
                        ],
                    ],
                ],

                [
                    'key' => 'secondary',
                    'data' => [
                        [
                            'key' => 'search',
                            'icon' => 'fa fa-search',
                            'click' => 'toggleSearch',
                        ],
                        [
                            'key' => 'theme',
                            'icon' => 'fa fa-moon',
                            'icons' => [
                                'theme' => [
                                    'dark' => [
                                        'icon' => 'fa fa-sun',
                                    ],
                                    'light' => [
                                        'icon' => 'fa fa-moon',
                                    ],
                                ],
                            ],
                            'click' => 'toggleTheme',
                        ],
                        [
                            'key' => 'site_desktop',
                            'icon' => 'fa fa-desktop',
                            'icons' => [
                                'site_status' => [
                                    '0' => [
                                        'icon' => 'fa fa-triangle-exclamation text-amber-400',
                                        'title' => '[(site_unavailable_message)]',
                                    ],
                                    '1' => [
                                        'icon' => 'fa fa-desktop relative',
                                    ],
                                ],
                            ],
                            'href' => '/',
                            'target' => '_blank',
                        ],
                        [
                            'key' => 'account',
                            'icon' => 'far fa-user-circle',
                            'image' => '[+user.photo+]',
                            'name' => '[+user.username+]',
                            'data' => [
                                [
                                    'key' => 'change_password',
                                    'icon' => 'fa fa-lock',
                                    'name' => '[%change_password%]',
                                    'click' => [
                                        'name' => 'PasswordChange',
                                    ],
                                    'permissions' => ['change_password'],
                                ],
                                [
                                    'key' => 'logout',
                                    'icon' => 'fa fa-sign-out',
                                    'name' => '[%logout%]',
                                    'click' => [
                                        'name' => 'Logout',
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
                                    'click' => [
                                        'name' => 'Configuration',
                                    ],
                                    'permissions' => ['settings'],
                                ],
                                [
                                    'key' => 'workspace',
                                    'icon' => 'fa fa-eye',
                                    'name' => '[%settings_ui%]',
                                    'click' => [
                                        'name' => 'Workspace',
                                    ],
                                    'permissions' => ['settings'],
                                ],
                                [
                                    'key' => 'site_schedule',
                                    'icon' => 'far fa-calendar',
                                    'name' => '[%site_schedule%]',
                                    'click' => [
                                        'name' => 'Schedules',
                                    ],
                                    'permissions' => ['view_eventlog'],
                                ],
                                [
                                    'key' => 'eventlog_viewer',
                                    'icon' => 'fa fa-exclamation-triangle',
                                    'name' => '[%eventlog_viewer%]',
                                    'click' => [
                                        'name' => 'EventLogs',
                                    ],
                                    'permissions' => ['view_eventlog'],
                                ],
                                [
                                    'key' => 'view_logging',
                                    'icon' => 'fa fa-user-secret',
                                    'name' => '[%view_logging%]',
                                    'click' => [
                                        'name' => 'SystemLog',
                                    ],
                                    'permissions' => ['logs'],
                                ],
                                [
                                    'key' => 'view_sysinfo',
                                    'icon' => 'fa fa-info',
                                    'name' => '[%view_sysinfo%]',
                                    'click' => [
                                        'name' => 'SystemInfo',
                                    ],
                                ],
                                [
                                    'key' => 'help',
                                    'icon' => 'far fa-question-circle',
                                    'name' => '[%help%]',
                                    'click' => [
                                        'name' => 'Help',
                                    ],
                                    'permissions' => ['help'],
                                ],
                                [
                                    'key' => 'settings_version',
                                    'name' => 'Evolution CE [(settings_version)]',
                                    'item.class' => 'text-center text-sm events-none',
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
                if (!Auth::user()->hasPermissions($item['permissions'])) {
                    unset($data[$k]);
                }
                unset($item['permissions']);
            } elseif (is_array($item)) {
                $item = $this->checkMenuPermissions($item);
            }

            if (isset($item['data'])) {
                if ($item['data']) {
                    //$item['data'] = array_values($item['data']);
                } elseif (empty($item['url'])) {
                    unset($data[$k]);
                }
            }
        }

        return $data;
    }

    /**
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
            '{"name":"PasswordChange"}' => Lang::get('global.change_password'),
            '{"name":"Logout"}' => Lang::get('global.logout'),
        ];

        return [
            'data' => array_map(fn($value, $key) => [
                'key' => $key,
                'value' => $value,
            ], $data, array_keys($data)),
        ];
    }
}
