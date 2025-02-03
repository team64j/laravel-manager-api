<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Request;
use Team64j\LaravelManagerApi\Models\SiteTemplate;

/**
 * @property SiteTemplate $resource
 */
class TemplateResource extends ApiResource
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource->exists) {
            $this->resource->setRawAttributes([
                'icon' => '',
                'category' => 0,
                'selectable' => 1,
            ]);
        }

        $bladeFile = current(config('view.paths')) . '/' . $this->resource->templatealias . '.blade.php';

        if (($request->input('createbladefile') || file_exists($bladeFile)) && $this->resource->templatealias) {
            $this->resource->setAttribute('content', file_get_contents($bladeFile));
        }

        return [
            'id' => $this->resource->getKey(),
            'attributes' => $this->resource->attributesToArray(),
            'tvs' => $this->resource->tvs->pluck('id'),
            'createbladefile' => 0
        ];
    }
}
