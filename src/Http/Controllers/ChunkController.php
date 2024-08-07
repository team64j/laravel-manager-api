<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteHtmlSnippet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ChunkRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\ChunkResource;
use Team64j\LaravelManagerApi\Layouts\ChunkLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class ChunkController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/chunks",
     *     summary="Получение списка чанков с пагинацией",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="name", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="category")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *         @OA\Parameter (name="groupBy", in="query", @OA\Schema(type="string", default="category")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     * @param ChunkLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(ChunkRequest $request, ChunkLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');
        $category = $request->input('category', -1);
        $filterName = $request->input('name');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'description', 'locked', 'disabled', 'category'];
        $groupBy = $request->input('groupBy');

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator $result */
        $result = SiteHtmlSnippet::withoutLocked()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('name', 'like', '%' . $filterName . '%'))
            ->when($category >= 0, fn($query) => $query->where('category', $category))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        if ($groupBy == 'category') {
            $callbackGroup = function ($group) {
                return [
                    'id' => $group->first()->category,
                    'name' => $group->first()->getRelation('category')->category ?? Lang::get('global.no_category'),
                    'data' => $group->map->withoutRelations(),
                ];
            };

            $data = $result->groupBy('category')
                ->map($callbackGroup)
                ->values();
        } else {
            $data = $result->map(fn($item) => $item->withoutRelations());
        }

        return ChunkResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                        'title' => $layout->titleList(),
                        'icon' => $layout->icon(),
                        'pagination' => $this->pagination($result),
                    ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []),
            ]);
    }

    /**
     * @OA\Post(
     *     path="/chunks",
     *     summary="Создание нового чанка",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     *
     * @return ChunkResource
     */
    public function store(ChunkRequest $request): ChunkResource
    {
        $model = SiteHtmlSnippet::query()->create($request->validated());

        return ChunkResource::make($model);
    }

    /**
     * @OA\Get(
     *     path="/chunks/{id}",
     *     summary="Чтение чанка",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     * @param string $id
     * @param ChunkLayout $layout
     *
     * @return ChunkResource
     */
    public function show(ChunkRequest $request, string $id, ChunkLayout $layout): ChunkResource
    {
        /** @var SiteHtmlSnippet $model */
        $model = SiteHtmlSnippet::query()->findOrNew($id);

        return ChunkResource::make($model)
            ->additional([
                'layout' => $layout->default($model),
                'meta' => [
                    'title' => $model->name ?? $layout->title(),
                    'icon' => $layout->icon(),
                ],
            ]);
    }

    /**
     * @OA\Put(
     *     path="/chunks/{id}",
     *     summary="Обновление чанка",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     * @param string $id
     *
     * @return ChunkResource
     */
    public function update(ChunkRequest $request, string $id): ChunkResource
    {
        /** @var SiteHtmlSnippet $model */
        $model = SiteHtmlSnippet::query()->findOrFail($id);

        $model->update($request->validated());

        return ChunkResource::make($model);
    }

    /**
     * @OA\Delete(
     *     path="/chunks/{id}",
     *     summary="Удаление чанка",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     * @param string $id
     *
     * @return Response
     */
    public function destroy(ChunkRequest $request, string $id): Response
    {
        /** @var SiteHtmlSnippet $model */
        $model = SiteHtmlSnippet::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/chunks/list",
     *     summary="Получение списка чанков с пагинацией для меню",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(ChunkRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteHtmlSnippet::withoutLocked()
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);

        return ChunkResource::collection($result->items())
            ->additional([
                'meta' => [
                    'route' => '/chunks/:id',
                    'pagination' => $this->pagination($result),
                    'prepend' => [
                        [
                            'name' => Lang::get('global.new_htmlsnippet'),
                            'icon' => 'fa fa-plus-circle',
                            'to' => [
                                'path' => '/chunks/new',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/chunks/tree",
     *     summary="Получение списка чанков с пагинацией для древовидного меню",
     *     tags={"Chunk"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="category", in="query", @OA\Schema(type="int", default="-1")),
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="opened", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ChunkRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(ChunkRequest $request): AnonymousResourceCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteHtmlSnippet::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SiteHtmlSnippet $item) => $item->setHidden(['category']));

            return ChunkResource::collection($result)
                ->additional([
                    'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
                ]);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteHtmlSnippet::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return ChunkResource::collection($result->map(fn(SiteHtmlSnippet $item) => $item->setHidden(['category'])))
                ->additional([
                    'meta' => [
                        'pagination' => $this->pagination($result),
                    ],
                ]);
        }

        $result = Category::query()
            ->whereHas('chunks')
            ->get();

        if (SiteHtmlSnippet::query()->where('category', 0)->exists()) {
            $result->add(new Category());
        }

        $result = $result->map(function ($category) use ($request, $settings) {
            $data = [
                'id' => $category->getKey() ?? 0,
                'name' => $category->category ?? Lang::get('global.no_category'),
                'category' => true,
            ];

            if (in_array((string) $data['id'], ($settings['opened'] ?? []), true)) {
                $request->query->replace([
                    'settings' => ['parent' => $data['id']] + $request->query('settings'),
                ]);

                $data += [
                    'data' => $result = $this->tree($request),
                    'pagination' => $result->additional['meta'],
                ];
            }

            return $data;
        })
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (Str::upper($a['name']) > Str::upper($b['name'])))
            ->values();

        return CategoryResource::collection($result)
            ->additional([
                'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
            ]);
    }
}
