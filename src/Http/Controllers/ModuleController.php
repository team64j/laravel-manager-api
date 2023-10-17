<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelEvolution\Models\SiteModule;
use Team64j\LaravelManagerApi\Http\Requests\ModuleRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\ModuleResource;
use Team64j\LaravelManagerApi\Layouts\ModuleLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;
use Throwable;

class ModuleController extends Controller
{
    use PaginationTrait;

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

        return ModuleResource::collection([
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

        $result = SiteModule::withoutLocked()
            ->orderBy('name')
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('disabled', Auth::user()->isAdmin() ? [0, 1] : [0])
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
                'data' => SiteModule::withoutLocked()
                    ->withoutProtected()
                    ->orderBy('name')
                    ->whereIn('disabled', Auth::user()->isAdmin() ? [0, 1] : [0])
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
     * @param ModuleRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(ModuleRequest $request): AnonymousResourceCollection
    {
        $category = $request->input('parent', -1);
        $filter = $request->input('filter');
        $opened = $request->string('opened')
            ->explode(',')
            ->filter(fn($i) => $i !== '')
            ->map(fn($i) => intval($i))
            ->values()
            ->toArray();

        $fields = ['id', 'name', 'description', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        /** @var LengthAwarePaginator $result */
        $result = SiteModule::withoutLocked()
            ->with('category')
            ->select($fields)
            ->when(!is_null($filter), fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->when($showFromCategory, fn($query) => $query->where('category', $category)->orderBy('name'))
            ->when(!$showFromCategory, fn($query) => $query->groupBy('category'))
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        if ($showFromCategory) {
            return ModuleResource::collection([
                'data' => [
                    'data' => $result->items(),
                    'pagination' => $this->pagination($result),
                ],
            ]);
        }

        return CategoryResource::collection([
            'data' => [
                'data' => $result->map(function (SiteModule $template) use ($request, $opened, $fields, $filter) {
                    /** @var Category $category */
                    $category = $template->getRelation('category') ?? new Category();
                    $category->id = $template->category;
                    $data = [];

                    if (in_array($category->getKey(), $opened, true)) {
                        $request->query->replace([
                            'parent' => $category->getKey(),
                        ]);

                        /* @var LengthAwarePaginator $result */
                        $result = $category->modules()
                            ->select($fields)
                            ->withoutLocked()
                            ->when(!is_null($filter), fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
                            ->orderBy('name')
                            ->paginate(Config::get('global.number_of_results'), ['*'], 'page', 1)
                            ->appends($request->all());

                        if ($result->isNotEmpty()) {
                            $data = [
                                'data' => [
                                    'data' => $result->items(),
                                    'pagination' => $this->pagination($result),
                                ],
                            ];
                        }
                    }

                    return [
                            'id' => $category->getKey(),
                            'name' => $category->category ?? Lang::get('global.no_category'),
                            'folder' => true,
                        ] + $data;
                })
                    ->sortBy('name')
                    ->values(),
            ],
        ]);
    }
}
