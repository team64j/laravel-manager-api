<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\SearchRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Models\SiteContent;

class SearchController extends Controller
{
    #[OA\Get(
        path: '/search',
        summary: 'Поиск',
        security: [['Api' => []]],
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(SearchRequest $request): JsonResourceCollection
    {
        $data = [];
        $search = $request->input('search');

        if (strlen($search) > 2) {
            $result = SiteContent::query()
                ->select([
                    'id',
                    'pagetitle as name',
                ])
                ->where('pagetitle', 'like', '%' . $search . '%')
                ->limit(config('global.number_of_results'))
                ->get()
                ->map(fn(SiteContent $i) => $i->setAttribute('route', 'Document'))
                ->toArray();

            $data = array_merge($data, $result);
        }

        return JsonResource::collection($data);
    }
}
