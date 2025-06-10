<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Request;
use Team64j\LaravelManagerApi\Models\SiteContent;

/**
 * @property SiteContent $resource
 */
class ResourceResource extends JsonResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'attributes' => $this->resource->attributesToArray(),
            'tvs' => $this->resource->tvs->pluck('value', 'name'),
            $this->mergeWhen(
                config('global.use_udperms'),
                fn() => [
                    'is_document_group' => $this->resource->documentGroups->isEmpty(),
                    'document_groups' => $this->resource->documentGroups->pluck('id'),
                ],
            ),
        ];
    }
}
