<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelEvolution\Models\SiteSnippet;
use Team64j\LaravelManagerApi\Http\Requests\SnippetRequest;
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

        return SnippetResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'filters' => [
                    'name',
                ],
            ],
        ])
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'tab' => $layout->titleList(),
                ],
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
     * @param string $snippet
     * @param SnippetLayout $layout
     *
     * @return SnippetResource
     */
    public function show(SnippetRequest $request, string $snippet, SnippetLayout $layout): SnippetResource
    {
        $snippet = SiteSnippet::query()->findOrNew($snippet);

        return SnippetResource::make($snippet)
            ->additional([
                'layout' => $layout->default($snippet),
                'meta' => [
                    'tab' => $layout->titleDefault($snippet),
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
     * @param SiteSnippet $snippet
     *
     * @return SnippetResource
     */
    public function update(SnippetRequest $request, SiteSnippet $snippet): SnippetResource
    {
        $snippet->update($request->validated());

        return new SnippetResource($snippet);
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
     * @param SiteSnippet $snippet
     *
     * @return Response
     */
    public function destroy(SnippetRequest $request, SiteSnippet $snippet): Response
    {
        $snippet->delete();

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

        $data = array_merge(
            [
                [
                    'name' => Lang::get('global.new_snippet'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Snippet',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ],
            ],
            $result->items()
        );

        return SnippetResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Snippet',
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/snippets/tree/{category}",
     *     summary="Получение списка сниппетов с пагинацией для древовидного меню",
     *     tags={"Snippets"},
     *     security={{"Api":{}}},
     *     parameters={
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
     * @param int $category
     *
     * @return AnonymousResourceCollection
     */
    public function tree(SnippetRequest $request, int $category): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
        $fields = ['id', 'name', 'description', 'category', 'locked', 'disabled'];

        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];

        if ($category >= 0) {
            $result = SiteSnippet::query()
                ->select($fields)
                ->where('category', $category)
                ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
                ->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            $data['data'] = $result->items();
            $data['pagination'] = $this->pagination($result);
        } else {
            $collection = Collection::make();

            $result = SiteSnippet::query()
                ->select($fields)
                ->where('category', 0)
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            if ($result->count()) {
                $collection->add(
                    [
                        'id' => 0,
                        'name' => Lang::get('global.no_category'),
                        'folder' => true,
                    ] + (in_array(0, $opened, true) ?
                        [
                            'data' => [
                                'data' => $result->items(),
                                'pagination' => $this->pagination($result),
                            ],
                        ]
                        : [])
                );
            }

            $result = Category::query()
                ->whereHas('snippets')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->getKey(),
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array($item->getKey(), $opened, true)) {
                        $result = $item->snippets()
                            ->paginate(Config::get('global.number_of_results'))
                            ->appends($request->all());

                        $data['data'] = [
                            'data' => $result->items(),
                            'pagination' => $this->pagination($result),
                        ];
                    }

                    $item->setRawAttributes($data);

                    return $item;
                });

            $data['data'] = $collection->merge($result);
        }

        return SnippetResource::collection([
            'data' => $data,
        ]);
    }
}
