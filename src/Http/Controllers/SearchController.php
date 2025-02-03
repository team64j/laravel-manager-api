<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\SearchRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Models\SiteContent;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class SearchController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/search",
     *     summary="Поиск",
     *     tags={"System"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param SearchRequest $request
     *
     * @return ApiCollection
     */
    public function index(SearchRequest $request): ApiCollection
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

        return ApiResource::collection($data);
    }
}
