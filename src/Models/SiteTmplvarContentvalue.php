<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteTmplvarContentvalue extends Model
{
    public $timestamps = false;

    protected $casts = [
        'tmplvarid' => 'int',
        'contentid' => 'int',
    ];

    protected $fillable = [
        'tmplvarid',
        'contentid',
        'value',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SiteContent::class, 'contentid', 'id');
    }

    public function tmplvar(): BelongsTo
    {
        return $this->belongsTo(SiteTmplvar::class, 'tmplvarid', 'id');
    }
}
