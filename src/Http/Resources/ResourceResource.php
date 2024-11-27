<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use EvolutionCMS\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @property SiteContent $resource
 */
class ResourceResource extends ApiResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        if ($request->has('template')) {
            $this->resource->template = $request->input('template');
        }

        if ($request->has('parent')) {
            $this->resource->parent = $request->input('parent');
        }

        if ($request->has('type')) {
            $this->resource->type = $request->input('type');
        }

        return [
            'id' => $this->resource->getKey(),
            'attributes' => $this->resource->attributesToArray(),
            'tvs' => $this->resource->getTvs()->pluck('value', 'name'),
            $this->mergeWhen(
                Config::get('global.use_udperms'),
                fn() => [
                    'is_document_group' => $this->resource->documentGroups->isEmpty(),
                    'document_groups' => $this->resource->documentGroups->pluck('id'),
                ],
            ),
        ];
    }
}
