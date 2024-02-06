<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Support;

use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL as FacadeUrl;

class Url
{
    /**
     * @return array|null
     */
    static public function getCurrentRoute(): ?array
    {
        return self::getRouteByPath(Request::getPathInfo());
    }

    /**
     * @param string $path
     *
     * @return string
     */
    static protected function pathToUrl(string $path): string
    {
        $prefix = Config::get('global.friendly_url_prefix', '');
        $suffix = Config::get('global.friendly_url_suffix', '');
        $secure = Config::get('global.server_protocol') == 'https';

        return FacadeUrl::to($prefix . trim($path, '/') . $suffix, [], $secure);
    }

    /**
     * @param int|null $id
     *
     * @return array|null
     */
    static public function getRouteById(int $id = null): ?array
    {
        if (!$id) {
            return null;
        }

        return Cache::store('file')
            ->rememberForever('cms.routes.' . $id, function () use ($id) {
                $routes = self::getParentsById($id, true);

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
                    $route['url'] = self::pathToUrl($path);

                    return $route;
                }

                return null;
            });
    }

    /**
     * @param string $path
     *
     * @return array|null
     */
    static public function getRouteByPath(string $path): ?array
    {
        $path = trim($path, '/');

        if (Cache::has('cms.routes.' . $path)) {
            return Cache::get('cms.routes.' . $path);
        }

        if ($path == '') {
            $route = self::getRouteById((int) Config::get('global.site_start'));

            if ($route) {
                Cache::forever('cms.routes.' . $path, $route);
            }

            return $route;
        }

        $route = null;
        $paths = explode('/', $path);
        $fields = ['id', 'parent', 'alias', 'isfolder', 'alias_visible'];

        $parents = SiteContent::query()
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
                    'url' => self::pathToUrl($path),
                ];

                break;
            }
        }

        if ($route) {
            Cache::forever('cms.routes.' . $path, $route);

            return $route;
        }

        return self::getRouteById((int) Config::get('global.error_page'));
    }

    /**
     * @param int $id
     * @param bool $current
     *
     * @return array
     */
    static public function getParentsById(int $id, bool $current = false): array
    {
        $parents = [];

        $fields = ['id', 'parent', 'alias', 'isfolder', 'alias_visible'];

        /** @var SiteContent $item */
        $item = SiteContent::query()
            ->select($fields)
            ->with('parents', fn($query) => $query->select($fields))
            ->firstWhere('id', $id);

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
    }
}
