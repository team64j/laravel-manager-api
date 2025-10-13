<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteTmplvarAccess extends Model
{
    public $timestamps = false;

    protected $table = 'site_tmplvar_access';

    protected $casts = [
        'tmplvarid'     => 'int',
        'documentgroup' => 'int',
    ];

    protected $fillable = [
        'tmplvarid',
        'documentgroup',
    ];

    public function tmplvar(): BelongsTo
    {
        return $this->belongsTo(SiteTmplvar::class, 'tmplvarid', 'id');
    }
}
