<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use Team64j\LaravelManagerApi\Traits\JsonResourceTrait;

/**
 * @property mixed $preserveKeys
 */
class JsonResource extends BaseResource
{
    use JsonResourceTrait;

    /**
     * @param $resource
     *
     * @return JsonResourceCollection
     */
    public static function collection($resource): JsonResourceCollection
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
     * @return JsonResourceCollection
     */
    protected static function newCollection($resource): JsonResourceCollection
    {
        return new JsonResourceCollection($resource, static::class);
    }
}
