<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Team64j\LaravelEvolution\Traits\LockedTrait;

/**
 * @property int $category
 * @property int $locked
 * @property int $disabled
 * @property string $name
 * @property string $description
 * @property string $plugincode
 * @property string $properties
 * @property Category $categories
 * @property SystemEventname[]|Collection $events
 * @method static Builder|SitePlugin activePhx()
 */
class SitePlugin extends Model
{
    use LockedTrait;

    public const CREATED_AT = 'createdon';
    public const UPDATED_AT = 'editedon';

    protected $table = 'site_plugins';

    protected $dateFormat = 'U';

    protected $casts = [
        'editor_type' => 'int',
        'category'    => 'int',
        'cache_type'  => 'bool',
        'locked'      => 'int',
        'disabled'    => 'int',
        'createdon'   => 'int',
        'editedon'    => 'int',
    ];

    protected $fillable = [
        'name',
        'description',
        'editor_type',
        'category',
        'cache_type',
        'plugincode',
        'locked',
        'properties',
        'disabled',
        'moduleguid',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category', 'id');
    }

    public function scopeActivePhx(Builder $builder): Builder
    {
        return $builder
            ->where('disabled', '!=', 1)
            ->where('plugincode', 'LIKE', "%phx.parser.class.inc.php%OnParseDocument();%");
    }

    public function events(): HasManyThrough
    {
        return $this->hasManyThrough(
            SystemEventname::class,
            SitePluginEvent::class,
            'pluginid',
            'id',
            'id',
            'evtid',
        );
    }
}
