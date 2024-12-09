<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Mixin;

use Closure;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

/**
 * @mixin URL
 * @see URL
 * @see \Illuminate\Routing\UrlGenerator
 */
class UrlMixin
{
    /**
     * @return Closure
     */
    public function getCurrentRoute(): Closure
    {
        return fn() => URL::getRouteByPath(Request::getPathInfo());
    }

    /**
     * @return Closure
     */
    public function pathToUrl(): Closure
    {
        return function (string $path): string
        {
            $prefix = Config::get('global.friendly_url_prefix', '');
            $suffix = Config::get('global.friendly_url_suffix', '');
            $secure = Config::get('global.server_protocol') == 'https';

            return URL::to($prefix . trim($path, '/') . $suffix, [], $secure);
        };
    }

    /**
     * @return Closure
     */
    public function getRouteById(): Closure
    {
        return function (int $id = null): ?array
        {
            if (!$id) {
                return null;
            }

            return Cache::store('file')
                ->rememberForever('cms.routes.' . $id, function () use ($id) {
                    $routes = URL::getParentsById($id, true);

                    if (!empty($routes[$id])) {
                        $siteStart = Config::get('global.site_start');
                        $route = $routes[$id];
                        $path = '/';

                        if ($id != $siteStart) {
                            $path = array_filter(
                                array_map(
                                    fn($i) => $i['alias_visible'] ? $i['alias'] : null,
                                    array_reverse($routes)
                                )
                            );

                            $path = '/' . implode('/', $path);
                        }

                        $route['path'] = $path;
                        $route['url'] = URL::pathToUrl($path);

                        return $route;
                    }

                    return null;
                });
        };
    }

    /**
     * @return Closure
     */
    public function getRouteByPath(): Closure
    {
        return function (string $path): ?array
        {
            $path = trim($path, '/');

            if (Cache::has('cms.routes.' . $path)) {
                return Cache::get('cms.routes.' . $path);
            }

            if ($path == '') {
                $route = URL::getRouteById((int) Config::get('global.site_start'));

                if ($route) {
                    Cache::forever('cms.routes.' . $path, $route);
                }

                return $route;
            }

            $route = null;
            $paths = explode('/', $path);
            $fields = ['id', 'parent', 'alias', 'isfolder', 'alias_visible'];

            $parents = SiteContent::withTrashed()
                ->select($fields)
                ->with('parents', fn($query) => $query->select($fields))
                ->where('alias', end($paths))
                ->get();

            /** @var SiteContent $item */
            foreach ($parents as $item) {
                $paths = [$item->alias];
                $parent = $item->parents;

                while ($parent) {
                    if ($parent->alias_visible) {
                        $paths[] = $parent->alias;
                    }

                    $parent = $parent->parents;
                }

                $paths = implode('/', array_reverse($paths));

                if ($path == $paths) {
                    $route = [
                        'id' => $item->id,
                        'parent' => $item->parent,
                        'alias' => $item->alias,
                        'isfolder' => $item->isfolder,
                        'alias_visible' => $item->alias_visible,
                        'path' => '/' . $path,
                        'url' => URL::pathToUrl($path),
                    ];

                    break;
                }
            }

            if ($route) {
                Cache::forever('cms.routes.' . $path, $route);

                return $route;
            }

            return URL::getRouteById((int) Config::get('global.error_page'));
        };
    }

    /**
     * @return Closure
     */
    public function getParentsById(): Closure
    {
        return function (int $id, bool $current = false): array
        {
            $parents = [];

            $fields = ['id', 'parent', 'alias', 'isfolder', 'alias_visible'];

            /** @var SiteContent $item */
            $item = SiteContent::withTrashed()
                ->select($fields)
                ->with('parents', fn($query) => $query->select($fields))
                ->firstWhere('id', $id);

            if (!$item) {
                return $parents;
            }

            if ($current || $item->parent == 0) {
                $parents[$item->id] = [
                    'id' => $item->id,
                    'parent' => $item->parent,
                    'alias' => $item->alias,
                    'isfolder' => $item->isfolder,
                    'alias_visible' => $item->alias_visible,
                ];
            }

            $parent = $item->parents;

            while ($parent) {
                $parents[$parent->id] = [
                    'id' => $parent->id,
                    'parent' => $parent->parent,
                    'alias' => $parent->alias,
                    'isfolder' => $parent->isfolder,
                    'alias_visible' => $parent->alias_visible,
                ];

                $parent = $parent->parents;
            }

            return $parents;
        };
    }
}
