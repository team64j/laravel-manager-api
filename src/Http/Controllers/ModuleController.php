<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\ModuleRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\ModuleLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteModule;

class ModuleController extends Controller
{
    public function __construct(protected ModuleLayout $layout) {}

    #[OA\Get(
        path: '/modules',
        summary: 'Получение списка модулей с пагинацией и фильтрацией',
        security: [['Api' => []]],
        tags: ['Module'],
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
    public function index(ModuleRequest $request): JsonResourceCollection
    {
        $filter = $request->input('filter');
        $category = $request->input('category', -1);
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
        $result = SiteModule::withoutLocked()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('name', 'like', '%' . $filterName . '%'))
            ->when($category >= 0, fn($query) => $query->where('category', $category))
            ->orderBy($order, $dir)
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        if ($groupBy == 'category') {
            $result->setCollection(
                $result
                    ->getCollection()
                    ->groupBy('category')
                    ->map(fn($group) => [
                        'id'   => $group->first()->category,
                        'name' => $group->first()->getRelation('category')->category ?? __('global.no_category'),
                        'data' => $group->map->withoutRelations(),
                    ])
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
                    'sorting' => [$order => $dir],
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
    }

    #[OA\Post(
        path: '/modules',
        summary: 'Создание нового модуля',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Module'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function store(ModuleRequest $request): JsonResource
    {
        $data = $request->validated();

        $data['modulecode'] = str($data['modulecode'] ?? '')->replaceFirst('<?php', '')->trim();

        $model = SiteModule::query()->create($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Get(
        path: '/modules/{id}',
        summary: 'Чтение модуля',
        security: [['Api' => []]],
        tags: ['Module'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(ModuleRequest $request, int $id): JsonResource
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
            $model->setAttribute('category', 0);
        }

        $model->setAttribute(
            'modulecode',
            "<?php\r\n" . str($model->modulecode ?? '')->replaceFirst('<?php', '')->trim()
        );

        return JsonResource::make($model)
            ->layout($this->layout->default($model));
    }

    #[OA\Put(
        path: '/modules/{id}',
        summary: 'Обновление модуля',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Module'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function update(ModuleRequest $request, int $id): JsonResource
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrFail($id);

        $data = $request->validated();

        $data['modulecode'] = str($data['modulecode'] ?? '')->replaceFirst('<?php', '')->trim();

        $model->update($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Delete(
        path: '/modules/{id}',
        summary: 'Удаление модуля',
        security: [['Api' => []]],
        tags: ['Module'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function destroy(ModuleRequest $request, int $id): Response
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/modules/list',
        summary: 'Получение списка модулей с пагинацией для меню',
        security: [['Api' => []]],
        tags: ['Module'],
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
    public function list(ModuleRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteModule::withoutLocked()
            ->orderBy('name')
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('disabled', auth()->user()->isAdmin() ? [0, 1] : [0])
            ->paginate(config('global.number_of_results'), [
                'id',
                'name',
                'locked',
                'disabled',
            ]);

        return JsonResource::collection($result)
            ->meta([
                'route'   => '/modules/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_module'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to'   => [
                            'path' => '/modules/0',
                        ],
                    ],
                ],
            ]);
    }

    #[OA\Get(
        path: '/modules/exec',
        summary: 'Получение списка модулей для запуска',
        security: [['Api' => []]],
        tags: ['Module'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'parent', in: 'query', schema: new OA\Schema(type: 'integer', default: -1)),
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
    public function exec(ModuleRequest $request): JsonResourceCollection
    {
        return JsonResource::collection(
            SiteModule::withoutLocked()
                ->withoutProtected()
                ->orderBy('name')
                ->whereIn('disabled', auth()->user()->isAdmin() ? [0, 1] : [0])
                ->get([
                    'id',
                    'name',
                ])
        )
            ->meta([
                'route' => '/modules/exec/:id',
            ]);
    }

    #[OA\Get(
        path: '/modules/exec/{id}',
        summary: 'Запуск модуля',
        security: [['Api' => []]],
        tags: ['Module'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok'
            ),
        ]
    )]
    public function run(ModuleRequest $request, string $module): mixed
    {
        /** @var SiteModule $module */
        $module = SiteModule::query()->findOrFail($module);

        //        try {
        $code = str_starts_with($module->modulecode, '<?php') ? '//' : '';

        chdir(app()->path());

        $modx = evo();

        if (!defined('IN_MANAGER_MODE')) {
            define('IN_MANAGER_MODE', true);
        }

        if (!session()->token()) {
            session()->put('_token', $request->input('token', ''));
        }

        $result = eval($code . $module->modulecode);
        //        } catch (Throwable $exception) {
        //            $result = $exception->getMessage();
        //        }

        return $result;
    }

    #[OA\Get(
        path: '/modules/tree',
        summary: 'Получение списка модулей с пагинацией для древовидного меню',
        security: [['Api' => []]],
        tags: ['Module'],
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
    public function tree(ModuleRequest $request): JsonResourceCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteModule::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SiteModule $item) => $item->setHidden(['category']));

            return JsonResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteModule::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return JsonResource::collection(
                $result->setCollection(
                    $result
                        ->getCollection()
                        ->map(fn(SiteModule $item) => [
                            'id'         => $item->id,
                            'title'      => $item->name,
                            'attributes' => $item,
                        ])
                )
            );
        }

        $result = Category::query()
            ->whereHas('modules')
            ->get();

        if (SiteModule::withoutLocked()->where('category', 0)->exists()) {
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
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (str($a['title'])->upper() > str($b['title'])->upper()))
            ->values();

        return JsonResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
    }
}
