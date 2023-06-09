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
use Team64j\LaravelEvolution\Models\SitePlugin;
use Team64j\LaravelEvolution\Models\SystemEventname;
use Team64j\LaravelManagerApi\Http\Requests\PluginRequest;
use Team64j\LaravelManagerApi\Http\Resources\PluginResource;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class PluginController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/plugins",
     *     summary="Получение списка плагинов с пагинацией и фильтрацией",
     *     tags={"Plugins"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="name", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="category")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PluginRequest $request
     * @param PluginLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(PluginRequest $request, PluginLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');
        $filterName = $request->input('name');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'description', 'locked', 'disabled', 'category'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator $result */
        $result = SitePlugin::withoutLocked()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('name', 'like', '%' . $filterName . '%'))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        return PluginResource::collection([
            'data' => [
                'data' => $result->groupBy('category')
                    ->map(fn($category) => [
                        'id' => $category->first()->category,
                        'name' => $category->first()->getRelation('category')->category ??
                            Lang::get('global.no_category'),
                        'data' => $category->map->withoutRelations(),
                    ])
                    ->values(),
                'pagination' => $this->pagination($result),
                'filters' => [
                    'name' => true,
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
     *     path="/plugins",
     *     summary="Создание нового плагина",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     *
     * @return PluginResource
     */
    public function store(PluginRequest $request): PluginResource
    {
        $plugin = SitePlugin::query()->create($request->validated());

        return new PluginResource($plugin);
    }

    /**
     * @OA\Get(
     *     path="/plugins/{id}",
     *     summary="Чтение плагина",
     *     tags={"Plugins"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PluginRequest $request
     * @param string $plugin
     * @param PluginLayout $layout
     *
     * @return PluginResource
     */
    public function show(PluginRequest $request, string $plugin, PluginLayout $layout): PluginResource
    {
        $plugin = SitePlugin::query()->findOrNew($plugin);

        return PluginResource::make($plugin)
            ->additional([
                'layout' => $layout->default($plugin),
                'meta' => [
                    'tab' => $layout->titleDefault($plugin),
                ],
            ]);
    }

    /**
     * @OA\Put(
     *     path="/plugins/{id}",
     *     summary="Обновление плагина",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     * @param SitePlugin $plugin
     *
     * @return PluginResource
     */
    public function update(PluginRequest $request, SitePlugin $plugin): PluginResource
    {
        $plugin->update($request->validated());

        return new PluginResource($plugin);
    }

    /**
     * @OA\Delete(
     *     path="/plugins/{id}",
     *     summary="Удаление плагина",
     *     tags={"Plugins"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PluginRequest $request
     * @param SitePlugin $plugin
     *
     * @return Response
     */
    public function destroy(PluginRequest $request, SitePlugin $plugin): Response
    {
        $plugin->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/plugins/list",
     *     summary="Получение списка плагинов с пагинацией для меню",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(PluginRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SitePlugin::withoutLocked()
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
                    'name' => Lang::get('global.new_plugin'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Plugin',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ],
            ],
            $result->items()
        );

        return PluginResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Plugin',
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/plugins/sort",
     *     summary="Получение списка всех плагинов для сортировки",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     * @param PluginLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function sort(PluginRequest $request, PluginLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');

        return PluginResource::collection([
            'data' => [
                'data' => SystemEventname::query()
                    ->with(
                        'plugins',
                        fn($q) => $q
                            ->select(['id', 'name', 'disabled', 'priority'])
                            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
                            ->orderBy('pivot_priority')
                    )
                    ->whereHas('plugins')
                    ->orderBy('name')
                    ->get()
                    ->map(function (SystemEventname $item) {
                        $item->setAttribute('data', $item->plugins);
                        $item->setAttribute('draggable', true);

                        return $item->withoutRelations();
                    }),
            ],
        ])
            ->additional([
                'layout' => $layout->sort(),
                'meta' => [
                    'tab' => $layout->titleSort(),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/plugins/tree/{category}",
     *     summary="Получение списка плагинов с пагинацией для древовидного меню",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     * @param int $category
     *
     * @return AnonymousResourceCollection
     */
    public function tree(PluginRequest $request, int $category): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
        $fields = ['id', 'name', 'description', 'category', 'locked', 'disabled'];

        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];

        if ($category >= 0) {
            $result = SitePlugin::query()
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

            $result = SitePlugin::query()
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
                ->whereHas('plugins')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->getKey(),
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array($item->getKey(), $opened, true)) {
                        $result = $item->plugins()
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

        return PluginResource::collection([
            'data' => $data,
        ]);
    }
}
