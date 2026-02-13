<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function delete(): void
    {
        throw_if(
            $this->templates->count(),
            message: 'Category is used in templates'
        );

        throw_if(
            $this->tvs->count(),
            message: 'Category is used in tvs'
        );

        throw_if(
            $this->chunks->count(),
            message: 'Category is used in chunks'
        );

        throw_if(
            $this->snippets->count(),
            message: 'Category is used in snippets'
        );

        throw_if(
            $this->plugins->count(),
            message: 'Category is used in plugins'
        );

        throw_if(
            $this->modules->count(),
            message: 'Category is used in modules'
        );

        parent::delete();
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
