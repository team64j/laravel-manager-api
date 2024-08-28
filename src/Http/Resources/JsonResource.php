<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use Team64j\LaravelManagerApi\Traits\ResourceTrait;

/**
 * @property mixed $preserveKeys
 */
class JsonResource extends BaseResource
{
    use ResourceTrait;

    /**
     * @param $resource
     *
     * @return ResourceCollection
     */
    public static function collection($resource): ResourceCollection
    {
        return tap(static::newCollection($resource), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    /**
     * @param $resource
     *
     * @return ResourceCollection
     */
    protected static function newCollection($resource): ResourceCollection
    {
        return new ResourceCollection($resource, static::class);
    }
}
