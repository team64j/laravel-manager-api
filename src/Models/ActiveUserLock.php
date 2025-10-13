<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveUserLock extends Model
{
    public $timestamps = false;

    protected $casts = [
        'internalKey' => 'int',
        'elementType' => 'int',
        'elementId'   => 'int',
        'lasthit'     => 'int',
    ];

    protected $fillable = [
        'sid',
        'internalKey',
        'elementType',
        'elementId',
        'lasthit',
    ];
}
