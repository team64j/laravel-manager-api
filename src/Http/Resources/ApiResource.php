<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use Team64j\LaravelManagerApi\Traits\ApiResourceTrait;

/**
 * @property mixed $preserveKeys
 */
class ApiResource extends BaseResource
{
    use ApiResourceTrait;

    /**
     * @param $resource
     *
     * @return ApiCollection
     */
    public static function collection($resource): ApiCollection
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
     * @return ApiCollection
     */
    protected static function newCollection($resource): ApiCollection
    {
        return new ApiCollection($resource, static::class);
    }
}
