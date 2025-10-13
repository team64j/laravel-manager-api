<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Team64j\LaravelEvolution\Traits\TimeMutatorTrait;

/**
 * @property int $type
 * @property int $user
 * @property int $eventid
 * @property string $source
 * @property string $createdon
 * @property string $description
 * @property User $users
 */
class EventLog extends Model
{
    use TimeMutatorTrait;

    protected $table = 'event_log';

    public const CREATED_AT = 'createdon';

    public const UPDATED_AT = null;

    protected $dateFormat = 'U';

    protected $casts = [
        'eventid'  => 'int',
        'type'     => 'int',
        'user'     => 'int',
        'usertype' => 'int',
    ];

    protected $fillable = [
        'eventid',
        'type',
        'user',
        'usertype',
        'source',
        'description',
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }
}
