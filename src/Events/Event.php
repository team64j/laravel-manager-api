<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Events;

use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use Team64j\LaravelManagerApi\Models\SystemEventname;

class Event
{
    public array $plugins = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $eventName = basename(static::class);

        if (Cache::has('events.' . $eventName)) {
            $pluginsNames = Cache::get('events.' . $eventName);

            foreach ($pluginsNames as $pluginsName) {
                $this->plugins[] = Cache::get('plugins.' . $pluginsName);
            }
        } else {
            $this->plugins = SystemEventname::with('plugins')
                ->withWhereHas('plugins', fn($query) => $query->where('disabled', 0))
                ->where('name', $eventName)
                ->firstOrNew()
                ->plugins
                ->toArray();

            foreach ($this->plugins as $plugin) {
                Cache::set('plugins.' . $plugin['name'], $plugin);
            }

            Cache::set('events.' . $eventName, array_column($this->plugins, 'name'));
        }
    }

    public function handle(array $params = []): ?array
    {
        extract($params);

        $responses = [];

        foreach ($this->plugins as $plugin) {
            $properties = array_map(
                fn($property) => current($property)['value'] ?? null,
                json_decode($plugin['properties'] ?? '{}', true) ?? []
            );
            extract($properties);
            $responses[] = eval($plugin['plugincode']);
        }

        return $this->render($responses) ?: null;
    }

    public function render(array $value): array
    {
        return $value;
    }
}
