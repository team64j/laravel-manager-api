<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelEvolution\Models\SiteModule;
use Team64j\LaravelManagerApi\Components\HelpIcon;
use Team64j\LaravelManagerApi\Http\Requests\ModuleRequest;
use Team64j\LaravelManagerApi\Http\Resources\ModuleResource;
use Team64j\LaravelManagerApi\Layouts\ModuleLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;
use Throwable;

class ModuleController extends Controller
{
    use PaginationTrait;

    /**
     * @var string
     */
    protected string $route = 'modules';

    /**
     * @return array
     */
    protected array $routes = [
        [
            'method' => 'get',
            'uri' => 'exec',
            'action' => [self::class, 'exec'],
        ],
        [
            'method' => ['get', 'post', 'delete', 'put'],
            'uri' => 'exec/{id}',
            'action' => [self::class, 'execRun'],
        ],
        [
            'method' => 'get',
            'uri' => 'tree',
            'action' => [self::class, 'tree'],
        ],
        [
            'method' => 'get',
            'uri' => 'list',
            'action' => [self::class, 'list'],
        ],
    ];

    /**
     * @OA\Get(
     *     path="/modules",
     *     summary="Получение списка модулей с пагинацией и фильтрацией",
     *     tags={"Module"},
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
     * @param ModuleRequest $request
     * @param ModuleLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(ModuleRequest $request, ModuleLayout $layout): AnonymousResourceCollection
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

        $result = SiteModule::query()
            ->select($fields)
            ->with('categories')
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('name', 'like', '%' . $filterName . '%'))
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $data = Collection::make([
            'data' => Collection::make(),
            'pagination' => $this->pagination($result),
            'filters' => [
                'name' => true,
            ],
        ]);

        /** @var SiteModule $item */
        foreach ($result->items() as $item) {
            if (!$data['data']->has($item->category)) {
                if ($item->category) {
                    $data['data'][$item->category] = [
                        'id' => $item->category,
                        'name' => $item->categories->category,
                        'data' => Collection::make(),
                    ];
                } else {
                    $data['data'][0] = [
                        'id' => 0,
                        'name' => Lang::get('global.no_category'),
                        'data' => Collection::make(),
                    ];
                }
            }

            $item->setAttribute(
                '#',
                HelpIcon::make(
                    $item->locked ? Lang::get('global.locked') : '',
                    'fa fa-cube fa-fw'
                )
                    ->setInnerIcon($item->locked ? 'fa fa-lock text-xs' : '')
                    ->isOpacity(false)
                    ->isFit()
            );

            $item->setAttribute('category.name', $data['data'][$item->category]['name']);
            $item->setAttribute('description.html', $item->description);

            $data['data'][$item->category]['data']->add($item->withoutRelations());
        }

        $data['data'] = $data['data']->values();

        return ModuleResource::collection([
            'data' => $data,
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
     *     path="/modules",
     *     summary="Создание нового модуля",
     *     tags={"Module"},
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
     * @param ModuleRequest $request
     *
     * @return ModuleResource
     */
    public function store(ModuleRequest $request): ModuleResource
    {
        $module = SiteModule::query()->create($request->validated());

        return new ModuleResource($module);
    }

    /**
     * @OA\Get(
     *     path="/modules/{id}",
     *     summary="Чтение модуля",
     *     tags={"Module"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ModuleRequest $request
     * @param string $module
     * @param ModuleLayout $layout
     *
     * @return ModuleResource
     */
    public function show(ModuleRequest $request, string $module, ModuleLayout $layout): ModuleResource
    {
        $module = SiteModule::query()->findOrNew($module);

        return ModuleResource::make($module)
            ->additional([
                'layout' => $layout->default($module),
                'meta' => [
                    'tab' => $layout->titleDefault($module),
                ],
            ]);
    }

    /**
     * @OA\Put(
     *     path="/modules/{id}",
     *     summary="Обновление модуля",
     *     tags={"Module"},
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
     * @param ModuleRequest $request
     * @param SiteModule $module
     *
     * @return ModuleResource
     */
    public function update(ModuleRequest $request, SiteModule $module): ModuleResource
    {
        $module->update($request->validated());

        return new ModuleResource($module);
    }

    /**
     * @OA\Delete(
     *     path="/modules/{id}",
     *     summary="Удаление модуля",
     *     tags={"Module"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ModuleRequest $request
     * @param SiteModule $module
     *
     * @return Response
     */
    public function destroy(ModuleRequest $request, SiteModule $module): Response
    {
        $module->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/modules/list",
     *     summary="Получение списка модулей с пагинацией для меню",
     *     tags={"Module"},
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
     * @param ModuleRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(ModuleRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteModule::query()
            ->orderBy('name')
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->whereIn('disabled', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'locked',
                'disabled',
            ]);

        $data = array_merge(
            [
                [
                    'name' => Lang::get('global.new_module'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Module',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ],
            ],
            $result->items()
        );

        return ModuleResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Module',
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/modules/exec",
     *     summary="Получение списка модулей для запуска",
     *     tags={"Module"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="integer", default="-1")),
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
     * @param ModuleRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function exec(ModuleRequest $request): AnonymousResourceCollection
    {
        return ModuleResource::collection([
            'data' => [
                'data' => SiteModule::query()
                    ->withoutProtected()
                    ->orderBy('name')
                    ->where('disabled', 0)
                    ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
                    ->whereIn('disabled', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
                    ->get([
                        'id',
                        'name',
                    ]),
                'route' => 'ModuleExec',
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/modules/exec/{id}",
     *     summary="Запуск модуля",
     *     tags={"Module"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *     )
     * )
     * @param ModuleRequest $request
     * @param string $module
     *
     * @return mixed|string
     */
    public function execRun(ModuleRequest $request, string $module): mixed
    {
        /** @var SiteModule $module */
        $module = SiteModule::query()->findOrFail($module);

        try {
            $code = str_starts_with($module->modulecode, '<?php') ? '//' : '';

            $result = eval($code . $module->modulecode);
        } catch (Throwable $exception) {
            $result = $exception->getMessage();
        }

        return $result;
    }

    /**
     * @OA\Get(
     *     path="/modules/tree",
     *     summary="Получение списка модулей с пагинацией для древовидного меню",
     *     tags={"Module"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="integer", default="-1")),
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
     * @param ModuleRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(ModuleRequest $request): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
        $category = $request->integer('parent', -1);
        $fields = ['id', 'name', 'description', 'category', 'locked', 'disabled'];

        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];

        if ($category >= 0) {
            $result = SiteModule::query()
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

            $result = SiteModule::query()
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
                ->whereHas('modules')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->id,
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array((int) $item->id, $opened, true)) {
                        $result = $item->modules()
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

        return ModuleResource::collection([
            'data' => $data,
        ]);
    }
}
