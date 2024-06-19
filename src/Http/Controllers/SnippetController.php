<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteSnippet;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\SnippetRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\SnippetResource;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class SnippetController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/snippets",
     *     summary="Получение списка сниппетов с пагинацией и фильтрацией",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     * @param SnippetLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(SnippetRequest $request, SnippetLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');
        $filterName = $request->input('name');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'description', 'locked', 'disabled', 'category'];
        $groupBy = $request->has('groupBy');

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator $result */
        $result = SiteSnippet::withoutLocked()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('name', 'like', '%' . $filterName . '%'))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        if ($groupBy) {
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

        return SnippetResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'title' => Lang::get('global.snippets'),
                    'icon' => $layout->getIcon(),
                    'pagination' => $this->pagination($result),
                    'filters' => [
                        'name',
                    ],
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []),
            ]);
    }

    /**
     * @OA\Post(
     *     path="/snippets",
     *     summary="Создание нового сниппета",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return SnippetResource
     */
    public function store(SnippetRequest $request): SnippetResource
    {
        $snippet = SiteSnippet::query()->create($request->validated());

        return new SnippetResource($snippet);
    }

    /**
     * @OA\Get(
     *     path="/snippets/{id}",
     *     summary="Чтение сниппета",
     *     tags={"Snippets"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param SnippetRequest $request
     * @param string $id
     * @param SnippetLayout $layout
     *
     * @return SnippetResource
     */
    public function show(SnippetRequest $request, string $id, SnippetLayout $layout): SnippetResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrNew($id);

        return SnippetResource::make($model)
            ->additional([
                'layout' => $layout->default($model),
                'meta' => [
                    'title' => $model->name ?? Lang::get('global.new_snippet'),
                    'icon' => $layout->getIcon(),
                ],
            ]);
    }

    /**
     * @OA\Put(
     *     path="/snippets/{id}",
     *     summary="Обновление сниппета",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     * @param string $id
     *
     * @return SnippetResource
     */
    public function update(SnippetRequest $request, string $id): SnippetResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);

        $model->update($request->validated());

        return SnippetResource::make($model);
    }

    /**
     * @OA\Delete(
     *     path="/snippets/{id}",
     *     summary="Удаление сниппета",
     *     tags={"Snippets"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param SnippetRequest $request
     * @param string $id
     *
     * @return Response
     */
    public function destroy(SnippetRequest $request, string $id): Response
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/snippets/list",
     *     summary="Получение списка сниппетов с пагинацией для меню",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(SnippetRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteSnippet::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('disabled', Auth::user()->isAdmin() ? [0, 1] : [0])
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);

        return SnippetResource::collection($result->items())
            ->additional([
                'meta' => [
                    'route' => '/snippets/:id',
                    'pagination' => $this->pagination($result),
                    'prepend' => [
                        [
                            'name' => Lang::get('global.new_snippet'),
                            'icon' => 'fa fa-plus-circle',
                            'to' => [
                                'path' => '/snippets/new',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/snippets/tree",
     *     summary="Получение списка сниппетов с пагинацией для древовидного меню",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(SnippetRequest $request): AnonymousResourceCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteSnippet::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SiteSnippet $item) => $item->setHidden(['category']));

            return SnippetResource::collection($result)
                ->additional([
                    'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
                ]);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteSnippet::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return SnippetResource::collection($result->map(fn(SiteSnippet $item) => $item->setHidden(['category'])))
                ->additional([
                    'meta' => [
                        'pagination' => $this->pagination($result),
                    ],
                ]);
        }

        $result = Category::query()
            ->whereHas('snippets')
            ->get();

        if (SiteSnippet::withoutLocked()->where('category', 0)->exists()) {
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
