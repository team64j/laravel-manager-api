<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property int $private_memgroup
 * @property int $private_webgroup
 * @property Collection $documents
 * @property Collection $memberGroups
 * @property Collection $webGroups
 */
class DocumentgroupName extends Model
{
    public $timestamps = false;

    protected $casts = [
        'private_memgroup' => 'int',
        'private_webgroup' => 'int',
    ];

    protected $fillable = [
        'name',
        'private_memgroup',
        'private_webgroup',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(
            SiteContent::class,
            'document_groups',
            'document_group',
            'document'
        );
    }

    public function memberGroups(): BelongsToMany
    {
        return $this->belongsToMany(MembergroupName::class, 'membergroup_access', 'documentgroup', 'membergroup');
    }
}
