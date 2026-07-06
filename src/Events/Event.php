<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Events;

use Team64j\LaravelManagerApi\Models\SitePlugin;

class Event
{
    public array $plugins = [];

    public function __construct() {}

    public static function handle(array $params = []): ?array
    {
        return SitePlugin::query()
            ->where('disabled', false)
            ->whereHas('events', fn($query) => $query->where('name', basename(static::class)))
            ->get()
            ->map(function (SitePlugin $plugin) use (&$responses, $params) {
                extract($params);
                extract(
                    array_map(
                        fn($property) => current($property)['value'] ?? null,
                        json_decode($plugin->properties ?? '{}', true) ?? []
                    )
                );
                try {
                    return eval($plugin->plugincode);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->toArray();
    }
}
