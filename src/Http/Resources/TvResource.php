<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use EvolutionCMS\Models\SiteTmplvar;
use Illuminate\Http\Request;

/**
 * @property SiteTmplvar $resource
 */
class TvResource extends ApiResource
{
    public function toArray(Request $request)
    {
        if (!$this->resource->exists) {
            $this->resource->setRawAttributes([
                'type' => 'text',
                'category' => 0,
                'rank' => 0,
            ]);
        }

        $this->resource->properties = $this->resource->properties ? json_encode(
            $this->resource->properties,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ) : '[]';

        $params = array_filter(explode('&', $this->resource->display_params ?? ''));
        $displayParamsData = [];

        foreach ($params as $param) {
            [$key, $value] = explode('=', $param);
            $displayParamsData[$key] = $value;
        }

        return [
            'id' => $this->resource->getKey(),
            'attributes' => $this->resource->attributesToArray(),
            'permissions' => $this->resource->permissions->pluck('id'),
            'templates' => $this->resource->templates->pluck('id'),
            'roles' => $this->resource->roles->pluck('id'),
            'display_params_data' => $displayParamsData
        ];
    }
}
