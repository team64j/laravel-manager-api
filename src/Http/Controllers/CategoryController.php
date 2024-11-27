<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\CategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class CategoryController extends Controller
{
    use PaginationTrait;

    public function __construct(protected CategoryLayout $layout)
    {
    }

    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Получение списка категорий с пагинацией",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="category", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
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
     * @param CategoryRequest $request
     *
     * @return ApiCollection
     */
    public function index(CategoryRequest $request): ApiCollection
    {
        $filter = $request->input('filter');
        $filterName = $request->input('category');
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'category', 'rank'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = Category::query()
            ->select($fields)
            ->when($filter, fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('category', 'like', '%' . $filterName . '%'))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        return ApiResource::collection($result->items())
            ->meta(
                [
                    'title' => Lang::get('global.category_management'),
                    'icon' => $this->layout->icon(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [])
            )
            ->layout($this->layout->list());
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Создание новой категории",
     *     tags={"Category"},
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
     * @param CategoryRequest $request
     *
     * @return ApiResource
     */
    public function store(CategoryRequest $request): ApiResource
    {
        $model = Category::query()->create($request->validated());

        return $this->show($request, $model->getKey());
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Чтение категории",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param CategoryRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function show(CategoryRequest $request, int $id): ApiResource
    {
        /** @var Category $model */
        $model = Category::query()->findOrNew($id);

        return ApiResource::make($model)
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $model->category ?? $this->layout->title(),
                'icon' => $this->layout->icon(),
            ]);
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     summary="Обновление категории",
     *     tags={"Category"},
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
     * @param CategoryRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function update(CategoryRequest $request, int $id): ApiResource
    {
        /** @var Category $model */
        $model = Category::query()->findOrFail($id);

        $model->update($request->validated());

        return $this->show($request, $model->getKey());
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Удаление категории",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param CategoryRequest $request
     * @param int $id
     *
     * @return Response
     */
    public function destroy(CategoryRequest $request, int $id): Response
    {
        /** @var Category $model */
        $model = Category::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/categories/sort",
     *     summary="Получение списка категорий для сортировки",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param CategoryRequest $request
     *
     * @return ApiCollection
     */
    public function sort(CategoryRequest $request): ApiCollection
    {
        return ApiResource::collection(Category::query()->orderBy('rank')->get())
            ->layout($this->layout->sort())
            ->meta([
                'title' => Lang::get('global.cm_sort_categories'),
                'icon' => $this->layout->iconSort(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/categories/select",
     *     summary="Получение списка категорий для выбора",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="selected", in="query", @OA\Schema(type="integer")),
     *         @OA\Parameter (name="itemNew", in="query", @OA\Schema(type="string", default="newcategory")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param CategoryRequest $request
     *
     * @return ApiCollection
     */
    public function select(CategoryRequest $request): ApiCollection
    {
        $selected = $request->integer('selected');

        return ApiResource::collection(
            Collection::make()
                ->add([
                    'key' => (string) $request->input('itemNew', 'newcategory'),
                    'value' => Lang::get('global.cm_create_new_category'),
                ])
                ->add([
                    'name' => Lang::get('global.category_management'),
                    'data' => Collection::make()
                        ->add(
                            new Category([
                                'key' => 0,
                                'category' => Lang::get('global.no_category'),
                            ])
                        )
                        ->merge(
                            Category::all()
                        )
                        ->map(fn(Category $category) => [
                            'key' => $category->id ?: 0,
                            'value' => $category->category,
                            'selected' => $selected == ($category->id ?: 0),
                        ]),
                ])
        );
    }

    /**
     * @OA\Get(
     *     path="/categories/tree",
     *     summary="Получение списка категорий с пагинацией для древовидного меню",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
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
     * @param CategoryRequest $request
     *
     * @return ApiCollection
     */
    public function tree(CategoryRequest $request): ApiCollection
    {
        $settings = $request->collect('settings');
        $filter = $request->input('filter');
        $order = $settings['order'] ?? 'id';
        $dir = $settings['dir'] ?? 'asc';
        $fields = ['id', 'category', 'rank'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = Category::query()
            ->when($filter, fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
            ->orderByRaw('upper(' . $order . ') ' . $dir)
            ->get()
            ->map(fn(Category $item) => [
                'id' => $item->getKey(),
                'title' => $item->category,
            ]);

        return ApiResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []);
    }

    /**
     * @OA\Get(
     *     path="/categories/list",
     *     summary="Получение списка категорий с пагинацией для меню",
     *     tags={"Category"},
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
     * @param CategoryRequest $request
     *
     * @return ApiCollection
     */
    public function list(CategoryRequest $request): ApiCollection
    {
        $filter = $request->input('filter');

        $result = Category::query()
            ->when(!is_null($filter), fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'category as name',
                'rank',
            ]);

        return ApiResource::collection($result->items())
            ->meta([
                'route' => '/categories/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => Lang::get('global.new_category'),
                        'icon' => 'fa fa-plus-circle',
                        'to' => [
                            'path' => '/categories/0',
                        ],
                    ],
                ],
            ]);
    }
}
