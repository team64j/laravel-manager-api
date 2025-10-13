<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Team64j\LaravelEvolution\Traits\TimeMutatorTrait;

/**
 * @property int $action
 * @property int $itemid
 * @property string $username
 * @property string $message
 * @property string $itemname
 */
class ManagerLog extends Model
{
    use TimeMutatorTrait;

    protected $table = 'manager_log';

    public $timestamps = false;

    protected $casts = [
        'timestamp'   => 'datetime',
        'internalKey' => 'int',
        'action'      => 'int',
        'itemid'      => 'int',
        'itemname'    => 'string',
    ];

    protected $fillable = [
        'timestamp',
        'internalKey',
        'username',
        'action',
        'itemid',
        'itemname',
        'message',
        'ip',
        'useragent',
    ];
}
