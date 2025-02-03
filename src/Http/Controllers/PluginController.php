<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\PluginRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SitePlugin;
use Team64j\LaravelManagerApi\Models\SystemEventname;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class PluginController extends Controller
{
    use PaginationTrait;

    public function __construct(protected PluginLayout $layout)
    {
    }

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
     *
     * @return ApiCollection
     */
    public function index(PluginRequest $request): ApiCollection
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
        $result = SitePlugin::withoutLocked()
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
                    'icon' => $this->layout->iconList(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
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
     * @return ApiResource
     */
    public function store(PluginRequest $request): ApiResource
    {
        $data = $request->validated();

        $data['plugincode'] = Str::replaceFirst('<?php', '', $data['plugincode'] ?? '');

        $model = SitePlugin::query()->create($data);

        return $this->show($request, $model->getKey());
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
     * @param int $id
     *
     * @return ApiResource
     */
    public function show(PluginRequest $request, int $id): ApiResource
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->with('events')->findOrNew($id);

        $model->setAttribute('category', $model->category ?? 0);
        $model->setAttribute('plugincode', "<?php\r\n" . $model->plugincode);
        $model->setAttribute('analyze', (int) !$model->exists);
        $model->setAttribute('events', $model->events->pluck('id'));

        return ApiResource::make($model->withoutRelations())
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->name),
                'icon' => $this->layout->icon(),
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
     * @param int $id
     *
     * @return ApiResource
     */
    public function update(PluginRequest $request, int $id): ApiResource
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->findOrFail($id);

        $data = $request->validated();

        $data['plugincode'] = Str::replaceFirst('<?php', '', $data['plugincode'] ?? '');

        $model->update($data);

        return $this->show($request, $model->getKey());
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
     * @param int $id
     *
     * @return Response
     */
    public function destroy(PluginRequest $request, int $id): Response
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->findOrFail($id);

        $model->delete();

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
     * @return ApiCollection
     */
    public function list(PluginRequest $request): ApiCollection
    {
        $filter = $request->get('filter');

        $result = SitePlugin::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('disabled', auth()->user()->isAdmin() ? [0, 1] : [0])
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
                'route' => '/plugins/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => __('global.new_plugin'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to' => [
                            'path' => '/plugins/0',
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
     *
     * @return ApiCollection
     */
    public function sort(PluginRequest $request): ApiCollection
    {
        $filter = $request->input('filter');

        return ApiResource::collection(
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
            ->layout($this->layout->sort())
            ->meta([
                'title' => $this->layout->titleSort(),
                'icon' => $this->layout->iconSort(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/plugins/events",
     *     summary="Получение списка событий плагинов с пагинацией",
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
     *
     * @return ApiCollection
     */
    public function events(PluginRequest $request)
    {
        $services = [
            'Parser Service Events',
            'Manager Access Events',
            'Web Access Service Events',
            'Cache Service Events',
            'Template Service Events',
            'User Defined Events',
        ];

        return ApiResource::collection(
            SystemEventname::query()
                ->orderByDesc('service')
                ->orderBy('groupname')
                ->orderBy('name')
                ->get()
                ->groupBy(fn($item) => $item->groupname ?: $services[$item->service])
                ->map(fn($item, $key) => [
                    'key' => md5($key),
                    'name' => $key,
                    'data' => $item,
                ])
                ->sortBy('name')
                ->values()
        );
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
     * @return ApiCollection
     */
    public function tree(PluginRequest $request): ApiCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SitePlugin::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SitePlugin $item) => $item->setHidden(['category']));

            return ApiResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SitePlugin::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return ApiResource::collection($result->map(fn(SitePlugin $item) => [
                'id' => $item->id,
                'title' => $item->name,
                'attributes' => $item,
            ]))
                ->meta([
                    'pagination' => $this->pagination($result),
                ]);
        }

        $result = Category::query()
            ->whereHas('plugins')
            ->get();

        if (SitePlugin::withoutLocked()->where('category', 0)->exists()) {
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
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (Str::upper($a['title']) > Str::upper($b['title'])))
            ->values();

        return ApiResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
    }
}
