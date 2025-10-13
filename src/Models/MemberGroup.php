<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_group
 * @property int $member
 */
class MemberGroup extends Model
{
    public $timestamps = false;

    protected $casts = [
        'user_group' => 'int',
        'member'     => 'int',
    ];

    protected $fillable = [
        'user_group',
        'member',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MembergroupName::class, 'user_group', 'id');
    }
}
