<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserRole extends Model
{
    public $timestamps = false;

    protected $casts = [
        'frames' => 'int',
        'home'   => 'int',
    ];

    protected $fillable = [
        'name',
        'description',
    ];

    public function tvs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                SiteTmplvar::class,
                (new UserRoleVar())->getTable(),
                'roleid',
                'tmplvarid'
            )->withPivot('rank')
            ->orderBy('pivot_rank', 'ASC');
    }
}
