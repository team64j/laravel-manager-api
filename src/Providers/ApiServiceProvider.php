<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Providers;

use EvolutionCMS\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Team64j\LaravelManagerApi\Http\Middleware\Authenticate;
use Team64j\LaravelManagerApi\Mixin\UrlMixin;
use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerApi\Models\User;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->bootMixin();

        if (!$this->app->runningInConsole()) {
            header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
            header('Access-Control-Allow-Headers: Accept, Authorization, X-Requested-With, Content-type');
            header('Access-Control-Allow-Methods: GET, PUT, POST, PATCH, DELETE, OPTIONS');

            $this->registerConfig();
            $this->registerLang();
            $this->registerPermissions();
        }
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

        $router->$method(Config::get('manager-api.guard.provider') . '.auth', Authenticate::class);
        //$router->$method('manager-api.permissions', PermissionsMiddleware::class);
    }

    /**
     * @return void
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__, 2) . '/routes/api.php');
    }

    /**
     * @return void
     */
    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/manager-api.php', 'manager-api');

        if (!$this->app->configurationIsCached()) {
            $guard = Config::get('manager-api.guard.provider');

            Config::set('auth.guards.' . $guard, Config::get('manager-api.guard'));
            Config::set('auth.providers.' . $guard, Config::get('manager-api.provider'));
            Config::set('database.connections.' . Config::get('database.default') . '.prefix', env('DB_PREFIX', ''));
        }
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

    /**
     * @return void
     */
    protected function registerPermissions(): void
    {
        Permissions::all()->map(function ($permission) {
            Gate::define($permission->key, fn(User $user) => $user->hasPermission($permission));
        });
    }

    /**
     * @return void
     */
    protected function bootMixin(): void
    {
        URL::mixin(new UrlMixin());
    }
}
