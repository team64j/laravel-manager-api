<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\PluginRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SitePlugin;
use Team64j\LaravelManagerApi\Models\SystemEventname;

class PluginController extends Controller
{
    public function __construct(protected PluginLayout $layout) {}

    #[OA\Get(
        path: '/plugins',
        summary: 'Получение списка плагинов с пагинацией и фильтрацией',
        security: [['Api' => []]],
        tags: ['Plugins'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', default: 'category')),
            new OA\Parameter(name: 'dir', in: 'query', schema: new OA\Schema(type: 'string', default: 'asc')),
            new OA\Parameter(name: 'groupBy', in: 'query', schema: new OA\Schema(type: 'string', default: 'category')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(PluginRequest $request): JsonResourceCollection
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
                    'id'   => $group->first()->category,
                    'name' => $group->first()->getRelation('category')->category ?? __('global.no_category'),
                    'data' => $group->map->withoutRelations(),
                ];
            };

            $result->setCollection(
                $result
                    ->getCollection()
                    ->groupBy('category')
                    ->map($callbackGroup)
                    ->values()
            );
        } else {
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(fn($item) => $item->withoutRelations())
            );
        }

        return JsonResource::collection($result)
            ->layout($this->layout->list())
            ->meta(
                [
                    'title' => $this->layout->titleList(),
                    'icon'  => $this->layout->iconList(),
                    'sorting' => [$order => $dir],
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
    }

    #[OA\Post(
        path: '/plugins',
        summary: 'Создание нового плагина',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Plugins'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function store(PluginRequest $request): JsonResource
    {
        $data = $request->validated();

        $data['plugincode'] = Str::replaceFirst('<?php', '', $data['plugincode'] ?? '');

        $model = SitePlugin::query()->create($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Get(
        path: '/plugins/{id}',
        summary: 'Чтение плагина',
        security: [['Api' => []]],
        tags: ['Plugins'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(PluginRequest $request, int $id): JsonResource
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->with('events')->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
            $model->setAttribute('category', 0);
        }

        $model->setAttribute('plugincode', "<?php\r\n" . $model->plugincode);
        $model->setAttribute('analyze', (int) !$model->exists);
        $model->setAttribute('events', $model->events->pluck('id'));

        return JsonResource::make($model->withoutRelations())
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->name),
                'icon'  => $this->layout->icon(),
            ]);
    }

    #[OA\Put(
        path: '/plugins/{id}',
        summary: 'Обновление плагина',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Plugins'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function update(PluginRequest $request, int $id): JsonResource
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->findOrFail($id);

        $data = $request->validated();

        $data['plugincode'] = Str::replaceFirst('<?php', '', $data['plugincode'] ?? '');

        $model->update($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Delete(
        path: '/plugins/{id}',
        summary: 'Удаление плагина',
        security: [['Api' => []]],
        tags: ['Plugins'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function destroy(PluginRequest $request, int $id): Response
    {
        /** @var SitePlugin $model */
        $model = SitePlugin::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/plugins/list',
        summary: 'Получение списка плагинов с пагинацией для меню',
        security: [['Api' => []]],
        tags: ['Plugins'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function list(PluginRequest $request): JsonResourceCollection
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

        return JsonResource::collection($result)
            ->meta([
                'route'   => '/plugins/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_plugin'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to'   => [
                            'path' => '/plugins/0',
                        ],
                    ],
                ],
            ]);
    }

    #[OA\Get(
        path: '/plugins/sort',
        summary: 'Получение списка всех плагинов для сортировки',
        security: [['Api' => []]],
        tags: ['Plugins'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function sort(PluginRequest $request): JsonResourceCollection
    {
        $filter = $request->input('filter');

        return JsonResource::collection(
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
                ->map(fn(SystemEventname $item) => $item
                    ->withoutRelations()
                    ->setAttribute('data', $item->plugins)
                //->setAttribute('draggable', 'priority')
                )
        )
            ->layout($this->layout->sort())
            ->meta([
                'title' => $this->layout->titleSort(),
                'icon'  => $this->layout->iconSort(),
            ]);
    }

    #[OA\Get(
        path: '/plugins/events',
        summary: 'Получение списка событий плагинов с пагинацией',
        security: [['Api' => []]],
        tags: ['Plugins'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
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

        return JsonResource::collection(
            SystemEventname::query()
                ->orderByDesc('service')
                ->orderBy('groupname')
                ->orderBy('name')
                ->get()
                ->groupBy(fn($item) => $item->groupname ?: $services[$item->service])
                ->map(fn($item, $key) => [
                    'key'  => md5($key),
                    'name' => $key,
                    'data' => $item,
                ])
                ->sortBy('name')
                ->values()
        );
    }

    #[OA\Get(
        path: '/plugins/tree',
        summary: 'Получение списка плагинов с пагинацией для древовидного меню',
        security: [['Api' => []]],
        tags: ['Plugins'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'int', default: -1)),
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'opened', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function tree(PluginRequest $request): JsonResourceCollection
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

            return JsonResource::collection($result)
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

            return JsonResource::collection(
                $result->setCollection(
                    $result
                        ->getCollection()
                        ->map(fn(SitePlugin $item) => [
                            'id'         => $item->id,
                            'title'      => $item->name,
                            'attributes' => $item,
                        ])
                )
            );
        }

        $result = Category::query()
            ->whereHas('plugins')
            ->get();

        if (SitePlugin::withoutLocked()->where('category', 0)->exists()) {
            $result->add(new Category());
        }

        $result = $result
            ->map(function ($category) use ($request, $settings) {
                $data = [
                    'id'       => $category->getKey() ?? 0,
                    'title'    => $category->category ?? __('global.no_category'),
                    'category' => true,
                ];

                if (in_array((string) $data['id'], ($settings['opened'] ?? []), true)) {
                    $request->query->replace([
                        'settings' => ['parent' => $data['id']] + $request->query('settings'),
                    ]);

                    $result = $this->tree($request)->toResponse($request)->getData();

                    $data['data'] = $result->data ?? [];
                    $data['meta'] = $result->meta ?? [];
                }

                return $data;
            })
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (Str::upper($a['title']) > Str::upper($b['title'])))
            ->values();

        return JsonResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
    }
}
