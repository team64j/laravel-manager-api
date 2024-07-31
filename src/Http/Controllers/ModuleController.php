<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteModule;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
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
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        if ($groupBy == 'category') {
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

        return ModuleResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                        'title' => $layout->titleList(),
                        'icon' => $layout->iconList(),
                        'pagination' => $this->pagination($result),
                    ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []),
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

        return ModuleResource::make($module);
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
     * @param string $id
     * @param ModuleLayout $layout
     *
     * @return ModuleResource
     */
    public function show(ModuleRequest $request, string $id, ModuleLayout $layout): ModuleResource
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrNew($id);

        return ModuleResource::make($model)
            ->additional([
                'layout' => $layout->default($model),
                'meta' => [
                    'title' => $layout->title($model->name),
                    'icon' => $layout->icon(),
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
     * @param string $id
     *
     * @return ModuleResource
     */
    public function update(ModuleRequest $request, string $id): ModuleResource
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrFail($id);

        $model->update($request->validated());

        return ModuleResource::make($model);
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
     * @param string $id
     *
     * @return Response
     */
    public function destroy(ModuleRequest $request, string $id): Response
    {
        /** @var SiteModule $model */
        $model = SiteModule::query()->findOrFail($id);

        $model->delete();

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

        return ModuleResource::collection($result->items())
            ->additional([
                'meta' => [
                    'route' => '/modules/:id',
                    'pagination' => $this->pagination($result),
                    'prepend' => [
                        [
                            'name' => Lang::get('global.new_module'),
                            'icon' => 'fa fa-plus-circle',
                            'to' => [
                                'path' => '/modules/new',
                            ],
                        ],
                    ],
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
        return ModuleResource::collection(
            SiteModule::withoutLocked()
                ->withoutProtected()
                ->orderBy('name')
                ->whereIn('disabled', Auth::user()->isAdmin() ? [0, 1] : [0])
                ->get([
                    'id',
                    'name',
                ])
        )
            ->additional([
                'meta' => [
                    'route' => '/modules/exec/:id',
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

            return ModuleResource::collection($result)
                ->additional([
                    'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
                ]);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteModule::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return ModuleResource::collection($result->map(fn(SiteModule $item) => $item->setHidden(['category'])))
                ->additional([
                    'meta' => [
                        'pagination' => $this->pagination($result),
                    ],
                ]);
        }

        $result = Category::query()
            ->whereHas('modules')
            ->get();

        if (SiteModule::withoutLocked()->where('category', 0)->exists()) {
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
