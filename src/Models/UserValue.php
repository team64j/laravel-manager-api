<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserValue extends Model
{
    public $timestamps = false;

    protected $casts = [
        'tmplvarid' => 'int',
        'userid'    => 'int',
    ];

    protected $fillable = [
        'tmplvarid',
        'userid',
        'value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userid', 'id');
    }

    public function tmplvar(): BelongsTo
    {
        return $this->belongsTo(SiteTmplvar::class, 'tmplvarid', 'id');
    }
}
