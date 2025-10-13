<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoleVar extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'tmplvarid' => 'int',
        'roleid'    => 'int',
        'rank'      => 'int',
    ];

    protected $fillable = [
        'tmplvarid',
        'roleid',
        'rank',
    ];

    public function tmplvar(): BelongsTo
    {
        return $this->belongsTo(SiteTmplvar::class, 'tmplvarid', 'id');
    }
}
