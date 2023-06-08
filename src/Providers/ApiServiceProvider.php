<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Team64j\LaravelEvolution\Models\SystemSetting;
use Team64j\LaravelManagerApi\Contracts\Http\Controller as ControllerContract;
use Team64j\LaravelManagerApi\Http\Middleware\Authenticate;
use Team64j\LaravelManagerApi\Http\Middleware\RedirectIfAuthenticated;
use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerApi\Models\User;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected array $middlewareAliases = [
        'manager.guest' => RedirectIfAuthenticated::class,
        'manager.auth' => Authenticate::class,
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Accept, Authorization, X-Requested-With, Content-type');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        $this->registerConfig();
        $this->registerLang();

        Permissions::all()->map(function ($permission) {
            Gate::define($permission->key, fn(User $user) => $user->hasPermissionOrFail($permission));
        });
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfig();
        $this->registerRoutes();
        $this->registerMiddlewares();
    }

    /**
     * @return void
     */
    protected function registerMiddlewares(): void
    {
        $router = $this->app['router'];

        $method = method_exists($router, 'aliasMiddleware') ? 'aliasMiddleware' : 'middleware';

        foreach ($this->middlewareAliases as $alias => $middleware) {
            $router->$method($alias, $middleware);
        }
    }

    /**
     * @return void
     */
    protected function registerRoutes(): void
    {
        $managerDir = 'manager';

        if (!Route::has('manager')) {
            Route::prefix($managerDir)
                ->name('manager')
                ->any('/', fn() => abort(404));
        }

        Route::prefix($managerDir . '/api')
            ->name('manager.api.')
            ->group(function (): void {
                $controllersNamespace = 'Team64j\LaravelManagerApi\Http\Controllers';

                Collection::make(
                    require_once $this->app->basePath('vendor/composer/autoload_classmap.php')
                )
                    ->keys()
                    ->filter(fn($controller) => Str::contains($controller, $controllersNamespace))
                    ->map(function ($controller) use ($controllersNamespace) {
                        $path = Str::of($controller)
                            ->replace($controllersNamespace, '\\')
                            ->replaceLast('Controller', '')
                            ->snake()
                            ->slug();

                        if ($path->isEmpty()) {
                            return;
                        }

                        /** @var ControllerContract $controllerClass */
                        $controllerClass = $this->app->make($controller);

                        if (!$controllerClass instanceof ControllerContract) {
                            return;
                        }

                        $path = $controllerClass->getRoute() ?: $path->toString();
                        $name = str_replace('/', '.', $path);

                        foreach ($controllerClass->getRoutes() as $route) {
                            $routeName = $name . '.' . Str::snake($route['name'] ?? $route['action'][1] ?? '');

                            Route::name($routeName)->match(
                                [$route['method']],
                                $path . '/' . $route['uri'],
                                $route['action']
                            )->middleware($route['middleware'] ?? 'manager.auth:manager');
                        }

                        Route::middleware(['manager.auth:manager'])->apiResource(
                            $path,
                            $controller,
                            $controllerClass->getRouteOptions()
                        );
                    });
            });
    }

    /**
     * @return void
     */
    protected function mergeConfig(): void
    {
        $auth = require realpath(__DIR__ . '/../../config/auth.php');
        Config::set('auth.guards.manager', $auth['guards']['manager']);
        Config::set('auth.providers.manager', $auth['providers']['manager']);
    }

    /**
     * @return void
     */
    protected function registerConfig(): void
    {
        if (!Config::has('global')) {
            Config::set(
                'global',
                SystemSetting::query()
                    ->pluck('setting_value', 'setting_name')
                    ->toArray()
            );
        }
    }

    /**
     * @return void
     */
    protected function registerLang(): void
    {
        $this->app->useLangPath(__DIR__ . '/../../lang');

        $this->app->setLocale(
            Str::lower(
                Str::substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? $this->app['config']['app.locale'], 0, 2)
            )
        );
    }
}
