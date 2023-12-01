<?php

namespace Team64j\LaravelManagerApi\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Lang;

trait PaginationTrait
{
    /**
     * @param LengthAwarePaginator $result
     *
     * @return array
     */
    protected function pagination(LengthAwarePaginator $result): array
    {
        $current = min($result->currentPage(), $result->lastPage());
        $from = $current > 1 ? (($current - 1) * $result->perPage()) + 1 : 1;
        $to = $current > 1 ? $result->perPage() * $current : $result->perPage();

        if ($to > $result->total()) {
            $to = $result->total();
        }

        return [
            'total' => $result->total(),
            //'prev' => str_replace(route('manager.api'), '', $result->previousPageUrl()),
            //'next' => str_replace(route('manager.api'), '', $result->nextPageUrl()),
            'prev' => $result->previousPageUrl(),
            'next' => $result->nextPageUrl(),
            'lang' => [
                'prev' => Lang::get('global.paging_prev'),
                'next' => Lang::get('global.paging_next'),
            ],
            'current' => $current,
            'per' => $result->perPage(),
            'info' => Lang::get('global.showing') . ' ' . $from . '-' . $to . '/' . $result->total(),
        ];
    }
}
