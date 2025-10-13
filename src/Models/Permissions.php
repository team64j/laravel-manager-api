<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $group_id
 * @property string $name
 * @property string $key
 * @property string $lang_key
 * @property PermissionsGroups $groups
 */
class Permissions extends Model
{
    protected $fillable = [
        'name',
        'key',
        'lang_key',
        'group_id',
        'disabled',
    ];

    public function groups(): HasOne
    {
        return $this->hasOne(PermissionsGroups::class, 'id', 'group_id');
    }

    public function rolePermissions(): HasOne
    {
        return $this->hasOne(RolePermissions::class, 'permission', 'key');
    }
}
