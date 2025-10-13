<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $lang_key
 * @property Collection|Permissions[] $permissions
 */
class PermissionsGroups extends Model
{
    protected $fillable = [
        'name',
        'lang_key',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permissions::class, 'group_id', 'id');
    }
}
