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
use Team64j\LaravelManagerApi\Http\Controllers\DocumentController;
use Team64j\LaravelManagerApi\Http\Controllers\DocumentsController;
use Team64j\LaravelManagerApi\Http\Controllers\EventLogController;
use Team64j\LaravelManagerApi\Http\Controllers\FileController;
use Team64j\LaravelManagerApi\Http\Controllers\FilesController;
use Team64j\LaravelManagerApi\Http\Controllers\HelpController;
use Team64j\LaravelManagerApi\Http\Controllers\ModuleController;
use Team64j\LaravelManagerApi\Http\Controllers\OpenApiController;
use Team64j\LaravelManagerApi\Http\Controllers\PasswordController;
use Team64j\LaravelManagerApi\Http\Controllers\PermissionController;
use Team64j\LaravelManagerApi\Http\Controllers\PluginController;
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
    ->middleware($authMiddleware)
    ->group(fn() => [
        /** Auth */
        Route::prefix('auth')
            ->group(fn() => [
                Route::post('/', [AuthController::class, 'login'])
                    ->withoutMiddleware($authMiddleware),
                Route::post('forgot', [AuthController::class, 'forgot'])
                    ->withoutMiddleware($authMiddleware),
                Route::post('refresh', [AuthController::class, 'refresh']),
            ]),

        /** Boostrap */
        Route::prefix('bootstrap')
            ->group(fn() => [
                Route::get('/', [BootstrapController::class, 'index']),
                Route::get('select-pages', [BootstrapController::class, 'selectPages']),
            ]),

        /** Cache */
        Route::prefix('cache')
            ->group(fn() => [
                Route::get('/', [CacheController::class, 'index']),
            ]),

        /** Categories */
        Route::prefix('categories')
            ->group(fn() => [
                Route::get('sort', [CategoryController::class, 'sort']),
                Route::get('select', [CategoryController::class, 'select']),
                Route::get('tree', [CategoryController::class, 'tree']),
                Route::get('list', [CategoryController::class, 'list']),
            ])
            ->apiResource('categories', CategoryController::class),

        /** Chunks */
        Route::prefix('chunks')
            ->group(fn() => [
                Route::get('tree/{category}', [ChunkController::class, 'tree']),
                Route::get('list', [ChunkController::class, 'list']),
            ])
            ->apiResource('chunks', ChunkController::class),

        /** Configuration */
        Route::apiResource('configuration', ConfigurationController::class)->only(['index', 'store']),

        /** Dashboard */
        Route::prefix('dashboard')
            ->group(fn() => [
                Route::get('sidebar', [DashboardController::class, 'sidebar']),
                Route::get('news', [DashboardController::class, 'news']),
                Route::get('news-security', [DashboardController::class, 'newsSecurity']),
            ])
            ->apiResource('dashboard', DashboardController::class)->only(['index']),

        /** Documents */
        Route::prefix('document')
            ->group(fn() => [
                Route::get('tree/{parent}', [DocumentController::class, 'tree']),
                Route::get('parents/{id}', [DocumentController::class, 'parents']),
            ])
            ->apiResource('document', DocumentController::class),

        Route::get('documents/{id}', [DocumentsController::class, 'show']),

        /** Event Logs */
        Route::prefix('event-log')
            ->group(fn() => [

            ])
            ->apiResource('event-log', EventLogController::class)->only(['index', 'show']),

        /** Files */
        Route::prefix('file')
            ->group(fn() => [
                Route::get('tree/{path}', [FileController::class, 'tree']),
            ])
            ->apiResource('file', FileController::class)->only(['show']),

        Route::prefix('files')
            ->group(fn() => [
                Route::get('tree', [FilesController::class, 'tree']),
            ])
            ->apiResource('files', FilesController::class)->only(['index', 'show']),

        /** Help */
        Route::get('help', [HelpController::class, 'index']),

        /** Modules */
        Route::prefix('modules')
            ->group(fn() => [
                Route::get('tree/{category}', [ModuleController::class, 'tree']),
                Route::get('list', [ModuleController::class, 'list']),
                Route::get('exec', [ModuleController::class, 'exec']),
                Route::addRoute(
                    ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                    'exec/{id}',
                    [ModuleController::class, 'execRun']
                ),
            ])
            ->apiResource('modules', ModuleController::class),

        /** Password */
        Route::apiResource('password', PasswordController::class)->only(['index', 'store']),

        /** Permissions */
        Route::prefix('permissions')
            ->group(fn() => [
                Route::get('groups', [PermissionController::class, 'groups']),
                Route::get('groups/{id}', [PermissionController::class, 'group']),
                Route::get('resources', [PermissionController::class, 'resources']),
                Route::get('resources/{id}', [PermissionController::class, 'resource']),
                Route::get('relations', [PermissionController::class, 'relations']),
                Route::get('relations/{id}', [PermissionController::class, 'relation']),
                Route::get('select', [PermissionController::class, 'select']),
            ]),

        /** Plugins */
        Route::prefix('plugins')
            ->group(fn() => [
                Route::get('sort', [PluginController::class, 'sort']),
                Route::get('tree/{category}', [PluginController::class, 'tree']),
                Route::get('list', [PluginController::class, 'list']),
            ])
            ->apiResource('plugins', PluginController::class),

        /** Roles */
        Route::prefix('roles')
            ->group(fn() => [
                Route::apiResource('categories', RoleCategoryController::class)->only(['index', 'show']),
                Route::apiResource('permissions', RolePermissionController::class)->only(['index', 'show']),
                Route::apiResource('users', RoleUserController::class)->only(['index', 'show']),
            ]),

        /** Schedules */
        Route::get('schedule', [ScheduleController::class, 'index']),

        /** Schedules */
        Route::get('search', [SearchController::class, 'index']),

        /** Snippets */
        Route::prefix('snippets')
            ->group(fn() => [
                Route::get('tree/{category}', [SnippetController::class, 'tree']),
                Route::get('list', [SnippetController::class, 'list']),
            ])
            ->apiResource('snippets', SnippetController::class),

        /** System Info */
        Route::prefix('system-info')
            ->group(fn() => [
                Route::get('phpinfo', [SystemInfoController::class, 'phpinfo']),
            ])
            ->apiResource('system-info', SystemInfoController::class)->only(['index']),

        /** System Logs */
        Route::get('system-log', [SystemLogController::class, 'index']),

        /** Templates */
        Route::prefix('templates')
            ->group(fn() => [
                Route::get('tree/{category}', [TemplateController::class, 'tree']),
                Route::get('list', [TemplateController::class, 'list']),
                Route::get('select', [TemplateController::class, 'select']),
                Route::get('{id}/tvs', [TemplateController::class, 'tvs']),
            ])
            ->apiResource('templates', TemplateController::class),

        /** Tvs */
        Route::prefix('tvs')
            ->group(fn() => [
                Route::get('tree/{category}', [TvController::class, 'tree']),
                Route::get('list', [TvController::class, 'list']),
                Route::get('sort', [TvController::class, 'sort']),
                Route::get('types', [TvController::class, 'types']),
            ])
            ->apiResource('tvs', TvController::class),

        /** Users */
        Route::prefix('users')
            ->group(fn() => [
                Route::get('list', [UserController::class, 'list']),
                Route::get('active', [UserController::class, 'active']),
            ])
            ->apiResource('users', UserController::class),

        /** Workspace */
        Route::apiResource('workspace', WorkspaceController::class)->only(['index', 'store']),
    ]);
