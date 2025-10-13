<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $document_group
 * @property int $document
 */
class DocumentGroup extends Model
{
    public $timestamps = false;

    protected $casts = [
        'document_group' => 'int',
        'document'       => 'int',
    ];

    protected $fillable = [
        'document_group',
        'document',
    ];
}
