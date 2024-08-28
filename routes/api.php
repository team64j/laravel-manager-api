<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Team64j\LaravelManagerApi\Http\Controllers\AuthController;
use Team64j\LaravelManagerApi\Http\Controllers\BootstrapController;
use Team64j\LaravelManagerApi\Http\Controllers\CacheController;
use Team64j\LaravelManagerApi\Http\Controllers\CategoryController;
use Team64j\LaravelManagerApi\Http\Controllers\ChunkController;
use Team64j\LaravelManagerApi\Http\Controllers\ConfigurationController;
use Team64j\LaravelManagerApi\Http\Controllers\DashboardController;
use Team64j\LaravelManagerApi\Http\Controllers\EventLogController;
use Team64j\LaravelManagerApi\Http\Controllers\FileController;
use Team64j\LaravelManagerApi\Http\Controllers\FilemanagerController;
use Team64j\LaravelManagerApi\Http\Controllers\HelpController;
use Team64j\LaravelManagerApi\Http\Controllers\ModuleController;
use Team64j\LaravelManagerApi\Http\Controllers\OpenApiController;
use Team64j\LaravelManagerApi\Http\Controllers\PasswordController;
use Team64j\LaravelManagerApi\Http\Controllers\PermissionController;
use Team64j\LaravelManagerApi\Http\Controllers\PluginController;
use Team64j\LaravelManagerApi\Http\Controllers\PreviewController;
use Team64j\LaravelManagerApi\Http\Controllers\ResourceController;
use Team64j\LaravelManagerApi\Http\Controllers\ResourcesController;
use Team64j\LaravelManagerApi\Http\Controllers\RoleCategoryController;
use Team64j\LaravelManagerApi\Http\Controllers\RolePermissionController;
use Team64j\LaravelManagerApi\Http\Controllers\RoleUserController;
use Team64j\LaravelManagerApi\Http\Controllers\ScheduleController;
use Team64j\LaravelManagerApi\Http\Controllers\SearchController;
use Team64j\LaravelManagerApi\Http\Controllers\SnippetController;
use Team64j\LaravelManagerApi\Http\Controllers\SystemInfoController;
use Team64j\LaravelManagerApi\Http\Controllers\SystemLogController;
use Team64j\LaravelManagerApi\Http\Controllers\TemplateController;
use Team64j\LaravelManagerApi\Http\Controllers\TvController;
use Team64j\LaravelManagerApi\Http\Controllers\UserController;
use Team64j\LaravelManagerApi\Http\Controllers\WorkspaceController;

$apiPath = Config::get('manager-api.uri', 'manager/api');
$authMiddleware = Config::get('manager-api.guard.provider') . '.auth:' . Config::get('manager-api.guard.provider');

Route::prefix($apiPath)
    ->name('manager.api')
    ->any('/', [OpenApiController::class, 'index']);

Route::prefix($apiPath)
    ->name('manager.api.')
    ->middleware([$authMiddleware/*, 'manager-api.permissions'*/])
    ->group(fn() => [
        /** Auth */
        Route::prefix('auth')
            ->group(fn() => [
                Route::withoutMiddleware($authMiddleware)
                    ->group(fn() => [
                        //Route::get('/', [AuthController::class, 'loginForm'])->name('auth.login-form'),
                        Route::post('/', [AuthController::class, 'login'])->name('auth.login'),
                        //Route::get('forgot', [AuthController::class, 'forgotForm'])->name('auth.forgot-form'),
                        //Route::post('forgot', [AuthController::class, 'forgot'])->name('auth.forgot'),
                    ]),

                Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh'),
            ]),

        /** Boostrap */
        Route::prefix('bootstrap')
            ->group(fn() => [
                Route::withoutMiddleware($authMiddleware)
                    ->get('/', [BootstrapController::class, 'init'])->name('bootstrap.init'),
                Route::post('/', [BootstrapController::class, 'index'])->name('bootstrap.index'),
                Route::get('select-pages', [BootstrapController::class, 'selectPages'])->name('bootstrap.select-pages'),
            ]),

        /** Cache */
        Route::prefix('cache')
            ->group(fn() => [
                Route::get('/', [CacheController::class, 'index'])->name('cache.index'),
            ]),

        /** Elements */
        Route::prefix('elements')
            ->group(fn() => [
                Route::get('templates', [TemplateController::class, 'index'])->name('elements.templates'),
                Route::get('tvs', [TvController::class, 'index'])->name('elements.tvs'),
                Route::get('chunks', [ChunkController::class, 'index'])->name('elements.chunks'),
                Route::get('snippets', [SnippetController::class, 'index'])->name('elements.snippets'),
                Route::get('modules', [ModuleController::class, 'index'])->name('elements.modules'),
                Route::get('plugins', [PluginController::class, 'index'])->name('elements.plugins'),
                Route::get('categories', [CategoryController::class, 'index'])->name('elements.categories'),
            ]),

        /** Categories */
        Route::prefix('categories')
            ->group(fn() => [
                Route::get('sort', [CategoryController::class, 'sort'])->name('categories.sort'),
                Route::get('select', [CategoryController::class, 'select'])->name('categories.select'),
                Route::get('tree', [CategoryController::class, 'tree'])->name('categories.tree'),
                Route::get('list', [CategoryController::class, 'list'])->name('categories.list'),
            ])
            ->apiResource('categories', CategoryController::class),

        /** Chunks */
        Route::prefix('chunks')
            ->group(fn() => [
                Route::get('tree', [ChunkController::class, 'tree'])->name('chunks.news'),
                Route::get('list', [ChunkController::class, 'list'])->name('chunks.list'),
            ])
            ->apiResource('chunks', ChunkController::class),

        /** Configuration */
        Route::apiResource('configuration', ConfigurationController::class)->only(['index', 'store']),

        /** Dashboard */
        Route::prefix('dashboard')
            ->group(fn() => [
                //Route::get('sidebar', [DashboardController::class, 'sidebar']),
                Route::get('news', [DashboardController::class, 'news'])->name('dashboard.news'),
                Route::get('news-security', [DashboardController::class, 'newsSecurity'])->name('dashboard.news-security'),
            ])
            ->apiResource('dashboard', DashboardController::class)->only(['index']),

        /** Event Logs */
        Route::prefix('event-log')
            ->group(fn() => [

            ])
            ->apiResource('event-log', EventLogController::class)->only(['index', 'show']),

        /** Files */
        Route::prefix('file')
            ->group(fn() => [
                Route::get('tree', [FileController::class, 'tree'])->name('file.tree'),
            ])
            ->apiResource('file', FileController::class)->only(['show']),

        Route::prefix('filemanager')
            ->group(fn() => [
                Route::get('tree', [FilemanagerController::class, 'tree'])->name('filemanager.tree'),
            ])
            ->apiResource('filemanager', FilemanagerController::class)->only(['index', 'show']),

        /** Help */
        Route::get('help', [HelpController::class, 'index'])->name('help'),

        /** Modules */
        Route::prefix('modules')
            ->group(fn() => [
                Route::get('tree', [ModuleController::class, 'tree'])->name('modules.tree'),
                Route::get('list', [ModuleController::class, 'list'])->name('modules.list'),
                Route::get('exec', [ModuleController::class, 'exec'])->name('modules.exec'),
                Route::addRoute(
                    ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                    'exec/{id}',
                    [ModuleController::class, 'run']
                )->name('modules.run'),
            ])
            ->apiResource('modules', ModuleController::class),

        /** Password */
        Route::apiResource('password', PasswordController::class)->only(['index', 'store']),

        /** Permissions */
        Route::prefix('permissions')
            ->group(fn() => [
                Route::get('groups', [PermissionController::class, 'groups'])->name('permissions.groups'),
                Route::get('groups/{id}', [PermissionController::class, 'group'])->name('permissions.group'),
                Route::get('resources', [PermissionController::class, 'resources'])->name('permissions.resources'),
                Route::get('resources/{id}', [PermissionController::class, 'resource'])->name('permissions.resource'),
                Route::get('relations', [PermissionController::class, 'relations'])->name('permissions.relations'),
                Route::get('relations/{id}', [PermissionController::class, 'relation'])->name('permissions.relation'),
                Route::get('select', [PermissionController::class, 'select'])->name('permissions.select'),
            ]),

        /** Preview */
        Route::get('preview/{id}', [PreviewController::class, 'index'])->name('preview'),

        /** Plugins */
        Route::prefix('plugins')
            ->group(fn() => [
                Route::get('sort', [PluginController::class, 'sort'])->name('plugins.sort'),
                Route::get('tree', [PluginController::class, 'tree'])->name('plugins.tree'),
                Route::get('list', [PluginController::class, 'list'])->name('plugins.list'),
            ])
            ->apiResource('plugins', PluginController::class),

        /** Resources */
        Route::prefix('resource')
            ->group(fn() => [
                Route::get('tree', [ResourceController::class, 'tree'])->name('resource.tree'),
                Route::get('parents/{id}', [ResourceController::class, 'parents'])->name('resource.parents'),
                Route::get('parents/{parent}/{id}', [ResourceController::class, 'setParent'])->name('resource.set-parent'),
            ])
            ->apiResource('resource', ResourceController::class),

        Route::get('resources/{id}', [ResourcesController::class, 'show'])->name('resources'),

        /** Roles */
        Route::prefix('roles')
            ->name('roles.')
            ->group(fn() => [
                Route::apiResource('categories', RoleCategoryController::class)->only(['index', 'show']),
                Route::apiResource('permissions', RolePermissionController::class)->only(['index', 'show']),
                Route::apiResource('users', RoleUserController::class)->only(['index', 'show']),
            ]),

        /** Schedules */
        Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index'),

        /** Schedules */
        Route::get('search', [SearchController::class, 'index'])->name('search.index'),

        /** Snippets */
        Route::prefix('snippets')
            ->group(fn() => [
                Route::get('tree', [SnippetController::class, 'tree'])->name('snippets.tree'),
                Route::get('list', [SnippetController::class, 'list'])->name('snippets.list'),
            ])
            ->apiResource('snippets', SnippetController::class),

        /** PHP Info */
        Route::get('phpinfo', [SystemInfoController::class, 'phpinfo'])->name('phpinfo'),

        /** System Info */
        Route::prefix('system-info')
            ->apiResource('system-info', SystemInfoController::class)->only(['index']),

        /** System Logs */
        Route::get('system-log', [SystemLogController::class, 'index'])->name('system-log.phpinfo'),

        /** Templates */
        Route::prefix('templates')
            ->group(fn() => [
                Route::get('tree', [TemplateController::class, 'tree'])->name('templates.tree'),
                Route::get('list', [TemplateController::class, 'list'])->name('templates.list'),
                Route::get('select', [TemplateController::class, 'select'])->name('templates.select'),
                Route::get('{id}/tvs', [TemplateController::class, 'tvs'])->name('templates.tvs'),
            ])
            ->apiResource('templates', TemplateController::class),

        /** Tvs */
        Route::prefix('tvs')
            ->group(fn() => [
                Route::get('tree', [TvController::class, 'tree'])->name('tvs.tree'),
                Route::get('list', [TvController::class, 'list'])->name('tvs.list'),
                Route::get('sort', [TvController::class, 'sort'])->name('tvs.sort'),
                Route::get('types', [TvController::class, 'types'])->name('tvs.types'),
                Route::get('display', [TvController::class, 'display'])->name('tvs.display'),
            ])
            ->apiResource('tvs', TvController::class),

        /** Users */
        Route::prefix('users')
            ->group(fn() => [
                Route::get('list', [UserController::class, 'list'])->name('users.list'),
                Route::get('active', [UserController::class, 'active'])->name('users.active'),
            ])
            ->apiResource('users', UserController::class),

        /** Workspace */
        Route::apiResource('workspace', WorkspaceController::class)->only(['index', 'store']),
    ]);
