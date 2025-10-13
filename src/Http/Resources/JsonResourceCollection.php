<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Traits\JsonResourceTrait;

class JsonResourceCollection extends AnonymousResourceCollection
{
    use JsonResourceTrait;

    /**
     * @uses \Illuminate\Http\Resources\Json\PaginatedResourceResponse::paginationInformation()
     * @see https://laravel.com/docs/12.x/eloquent-resources#customizing-the-pagination-information
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        $current = min($default['meta']['current_page'], $default['meta']['last_page']);
        $from = $current > 1 ? (($current - 1) * $default['meta']['per_page']) + 1 : 1;
        $to = $current > 1 ? $default['meta']['per_page'] * $current : $default['meta']['per_page'];

        if ($to > $default['meta']['total']) {
            $to = $default['meta']['total'];
        }

        return [
            'meta' => [
                'pagination' => [
                    'total'   => $default['meta']['total'],
                    //'prev' => str_replace(route('manager.api'), '', $result->previousPageUrl()),
                    //'next' => str_replace(route('manager.api'), '', $result->nextPageUrl()),
                    'prev'    => $default['links']['prev'],
                    'next'    => $default['links']['next'],
                    'lang'    => [
                        'prev' => Lang::get('global.paging_prev'),
                        'next' => Lang::get('global.paging_next'),
                    ],
                    'current' => $current,
                    'per'     => $default['meta']['per_page'],
                    'info'    => Lang::get('global.showing') . ' ' . $from . '-' . $to . '/'
                        . $default['meta']['total'],
                ],
            ],
        ];
    }
}
