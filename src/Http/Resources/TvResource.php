<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Request;
use Team64j\LaravelManagerApi\Models\SiteTmplvar;

/**
 * @property SiteTmplvar $resource
 */
class TvResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (!$this->resource->properties) {
            $this->resource->properties = '[]';
        }

        $params = array_filter(explode('&', $this->resource->display_params ?? ''));
        $displayParamsData = [];

        foreach ($params as $param) {
            [$key, $value] = explode('=', $param);
            $displayParamsData[$key] = $value;
        }

        return [
            'id'                  => (int) $this->resource->getKey(),
            'attributes'          => $this->resource->attributesToArray(),
            'permissions'         => $this->resource->permissions->pluck('id'),
            'templates'           => $this->resource->templates->pluck('id'),
            'roles'               => $this->resource->roles->pluck('id'),
            'display_params_data' => $displayParamsData,
        ];
    }
}
