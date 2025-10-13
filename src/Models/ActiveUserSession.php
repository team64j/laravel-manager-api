<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Team64j\LaravelEvolution\Traits\TimeMutatorTrait;

class ActiveUserSession extends Model
{
    use TimeMutatorTrait;

    protected $primaryKey = 'sid';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'internalKey' => 'int',
        'lasthit'     => 'datetime',
    ];

    protected $fillable = [
        'sid',
        'internalKey',
        'lasthit',
        'ip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'internalKey', 'id');
    }
}
