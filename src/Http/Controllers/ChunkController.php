<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ChunkRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\ChunkLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteHtmlSnippet;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class ChunkController extends Controller
{
    use PaginationTrait;

    /**
     * @param ChunkLayout $layout
     */
    public function __construct(protected ChunkLayout $layout)
    {
    }

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
     *
     * @return ApiCollection
     */
    public function index(ChunkRequest $request): ApiCollection
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
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        if ($groupBy == 'category') {
            $callbackGroup = function ($group) {
                return [
                    'id' => $group->first()->category,
                    'name' => $group->first()->getRelation('category')->category ?? __('global.no_category'),
                    'data' => $group->map->withoutRelations(),
                ];
            };

            $data = $result->groupBy('category')
                ->map($callbackGroup)
                ->values();
        } else {
            $data = $result->map(fn($item) => $item->withoutRelations());
        }

        return ApiResource::collection($data)
            ->layout($this->layout->list())
            ->meta(
                [
                    'title' => $this->layout->titleList(),
                    'icon' => $this->layout->icon(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
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
     * @return ApiResource
     */
    public function store(ChunkRequest $request): ApiResource
    {
        $model = SiteHtmlSnippet::query()->create($request->validated());

        return $this->show($request, $model->getKey());
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
     * @param int $id
     *
     * @return ApiResource
     */
    public function show(ChunkRequest $request, int $id): ApiResource
    {
        /** @var SiteHtmlSnippet $model */
        $model = SiteHtmlSnippet::query()->findOrNew($id);

        if (!$model->exists) {
            $model->setRawAttributes([
                'category' => 0,
            ]);
        }

        return ApiResource::make($model)
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $model->name ?? $this->layout->title(),
                'icon' => $this->layout->icon(),
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
     * @param int $id
     *
     * @return ApiResource
     */
    public function update(ChunkRequest $request, int $id): ApiResource
    {
        /** @var SiteHtmlSnippet $model */
        $model = SiteHtmlSnippet::query()->findOrFail($id);

        $model->update($request->validated());

        return $this->show($request, $model->getKey());
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
     * @param int $id
     *
     * @return Response
     */
    public function destroy(ChunkRequest $request, int $id): Response
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
     * @return ApiCollection
     */
    public function list(ChunkRequest $request): ApiCollection
    {
        $filter = $request->get('filter');

        $result = SiteHtmlSnippet::withoutLocked()
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->orderBy('name')
            ->paginate(config('global.number_of_results'), [
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);

        return ApiResource::collection($result->items())
            ->meta([
                'route' => '/chunks/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => __('global.new_htmlsnippet'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to' => [
                            'path' => '/chunks/0',
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
     * @return ApiCollection
     */
    public function tree(ChunkRequest $request): ApiCollection
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

            return ApiResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteHtmlSnippet::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return ApiResource::collection($result->map(fn(SiteHtmlSnippet $item) => [
                'id' => $item->id,
                'title' => $item->name,
                'attributes' => $item,
            ]))
                ->meta([
                    'pagination' => $this->pagination($result),
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
                'title' => $category->category ?? __('global.no_category'),
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
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (str($a['title'])->upper() > str($b['title'])->upper()))
            ->values();

        return ApiResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
    }
}
