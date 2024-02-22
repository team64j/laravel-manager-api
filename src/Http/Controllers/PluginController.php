<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SitePlugin;
use EvolutionCMS\Models\SystemEventname;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\PluginRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
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
        $groupBy = $request->has('groupBy');

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

        return PluginResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                        'title' => Lang::get('global.plugins'),
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

        return PluginResource::make($plugin);
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
                    'title' => $plugin->name ?? Lang::get('global.new_plugin'),
                    'icon' => $layout->getIcon(),
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

        return PluginResource::make($plugin);
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

        return PluginResource::collection($result->items())
            ->additional([
                'meta' => [
                    'name' => 'Plugin',
                    'pagination' => $this->pagination($result),
                    'prepend' => [
                        [
                            'name' => Lang::get('global.new_plugin'),
                            'icon' => 'fa fa-plus-circle',
                            'to' => [
                                'name' => 'Plugin',
                                'params' => [
                                    'id' => 'new',
                                ],
                            ],
                        ],
                    ],
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

        return PluginResource::collection(
            SystemEventname::query()
                ->with(
                    'plugins',
                    fn($q) => $q
                        ->select(['id', 'name', 'priority'])
                        ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
                        ->orderBy('pivot_priority')
                )
                ->whereHas('plugins')
                ->orderBy('name')
                ->get()
                ->map(fn(SystemEventname $item) => $item->withoutRelations()
                    ->setAttribute('data', $item->plugins)
                    //->setAttribute('draggable', 'priority')
                )
        )
            ->additional([
                'layout' => $layout->sort(),
                'meta' => [
                    'title' => Lang::get('global.plugin_priority_title'),
                    'icon' => $layout->getIconSort(),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/plugins/tree",
     *     summary="Получение списка плагинов с пагинацией для древовидного меню",
     *     tags={"Plugins"},
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
     * @param PluginRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(PluginRequest $request): AnonymousResourceCollection
    {
        $category = $request->input('parent', -1);
        $settings = $request->collect('settings');
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'description', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SitePlugin::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get();

            return PluginResource::collection($result)
                ->additional([
                    'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
                ]);
        }

        /** @var LengthAwarePaginator $result */
        $result = SitePlugin::withoutLocked()
            ->with('category')
            ->select($fields)
            ->when($showFromCategory, fn($query) => $query->where('category', $category)->orderBy('name'))
            ->when(!$showFromCategory, fn($query) => $query->groupBy('category'))
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        if ($showFromCategory) {
            return PluginResource::collection($result->items())
                ->additional([
                    'meta' => [
                        'pagination' => $this->pagination($result),
                    ],
                ]);
        }

        $result = $result->map(function (SitePlugin $template) use ($request, $settings, $fields) {
            /** @var Category $category */
            $category = $template->getRelation('category') ?? new Category();
            $category->id = $template->category;
            $data = [];

            if (in_array((string) $category->getKey(), ($settings['opened'] ?? []), true)) {
                $request->query->replace([
                    'parent' => $category->getKey(),
                ]);

                /* @var LengthAwarePaginator $result */
                $result = $category->plugins()
                    ->select($fields)
                    ->withoutLocked()
                    ->orderBy('name')
                    ->paginate(Config::get('global.number_of_results'), ['*'], 'page', 1)
                    ->appends($request->all());

                if ($result->isNotEmpty()) {
                    $data = [
                        'data' => $result->items(),
                        'pagination' => $this->pagination($result),
                    ];
                }
            }

            return [
                    'id' => $category->getKey(),
                    'name' => $category->category ?? Lang::get('global.no_category'),
                    'folder' => true,
                ] + $data;
        })
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (Str::upper($a['name']) > Str::upper($b['name'])))
            ->values();

        return CategoryResource::collection($result)
            ->additional([
                'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
            ]);
    }
}
