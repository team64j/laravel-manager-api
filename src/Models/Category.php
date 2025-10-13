<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $category
 * @property SiteTmplvar[]|Collection $tvs
 * @property SiteTemplate[]|Collection $templates
 * @property SiteHtmlSnippet[]|Collection $chunks
 * @property SiteSnippet[]|Collection $snippets
 * @property SitePlugin[]|Collection $plugins
 * @property SiteModule[]|Collection $modules
 */
class Category extends Model
{
    public $timestamps = false;

    protected $casts = [
        'rank'     => 'int',
        'category' => 'string',
    ];

    protected $fillable = [
        'category',
        'rank',
    ];

    public static function templatesNoCategory(): \Illuminate\Support\Collection
    {
        $data = SiteTemplate::query()
            ->select([
                'id',
                'templatename as name',
                'templatealias as alias',
                'description',
                'locked',
                'category',
            ])
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public static function tvsNoCategory(array $ids = [], bool $not = false): \Illuminate\Support\Collection
    {
        $data = SiteTmplvar::query()
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'category',
            ])
            ->where(fn($item) => $ids ? ($not ? $item->whereKeyNot($ids) : $item->whereKey($ids)) : null)
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public static function chunksNoCategory(): \Illuminate\Support\Collection
    {
        $data = SiteHtmlSnippet::query()
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ])
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public static function snippetsNoCategory(): \Illuminate\Support\Collection
    {
        $data = SiteSnippet::query()
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ])
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public static function pluginsNoCategory(): \Illuminate\Support\Collection
    {
        $data = SitePlugin::query()
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ])
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public static function modulesNoCategory(): \Illuminate\Support\Collection
    {
        $data = SiteModule::query()
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ])
            ->where('category', 0)
            ->paginate(Config::get('global.number_of_results'));

        return $data->isNotEmpty() ? \Illuminate\Support\Collection::make([
            'id'   => 0,
            'name' => __('global.no_category'),
            'rank' => 0,
        ])
            ->merge($data) : collect();
    }

    public function templates(): HasMany
    {
        return $this
            ->hasMany(SiteTemplate::class, 'category', 'id')
            ->select([
                'id',
                'templatename as name',
                'templatealias as alias',
                'description',
                'locked',
                'category',
            ]);
    }

    public function tvs(): HasMany
    {
        return $this
            ->hasMany(SiteTmplvar::class, 'category', 'id')
            ->select([
                'id',
                'name',
                'caption as description',
                'description as intro',
                'locked',
                'category',
            ]);
    }

    public function chunks(): HasMany
    {
        return $this
            ->hasMany(SiteHtmlSnippet::class, 'category', 'id')
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);
    }

    public function snippets(): HasMany
    {
        return $this
            ->hasMany(SiteSnippet::class, 'category', 'id')
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);
    }

    public function plugins(): HasMany
    {
        return $this
            ->hasMany(SitePlugin::class, 'category', 'id')
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);
    }

    public function modules(): HasMany
    {
        return $this
            ->hasMany(SiteModule::class, 'category', 'id')
            ->select([
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);
    }
}
