<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Team64j\LaravelManagerApi\Http\Requests\CategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Models\Category;

class CategoryController extends Controller
{
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
     * @return JsonResourceCollection
     */
    public function index(CategoryRequest $request): JsonResourceCollection
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
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        return JsonResource::collection($result)
            ->meta(
                [
                    'title' => __('global.category_management'),
                    'icon' => $this->layout->icon(),
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
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
     * @return JsonResource
     */
    public function store(CategoryRequest $request): JsonResource
    {
        return $this->show(
            $request,
            Category::query()->create($request->validated())->getKey()
        );
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
     * @return JsonResource
     */
    public function show(CategoryRequest $request, int $id): JsonResource
    {
        /** @var Category $model */
        $model = Category::query()->findOrNew($id);

        return JsonResource::make($model)
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
     * @return JsonResource
     */
    public function update(CategoryRequest $request, int $id): JsonResource
    {
        /** @var Category $model */
        $model = tap(Category::query()->findOrFail($id))
            ->update($request->validated());

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
        Category::query()->findOrFail($id)->delete();

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
     * @return JsonResourceCollection
     */
    public function sort(CategoryRequest $request): JsonResourceCollection
    {
        return JsonResource::collection(
            Category::query()
                ->orderBy('rank')
                ->get()
        )
            ->layout($this->layout->sort())
            ->meta([
                'title' => __('global.cm_sort_categories'),
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
     * @return JsonResourceCollection
     */
    public function select(CategoryRequest $request): JsonResourceCollection
    {
        $selected = $request->collect('selected');

        return JsonResource::collection(
            collect()
                ->add([
                    'key' => (string) $request->input('itemNew', 'newcategory'),
                    'value' => __('global.cm_create_new_category'),
                ])
                ->add([
                    'name' => __('global.category_management'),
                    'data' => collect()
                        ->add(
                            new Category([
                                'key' => 0,
                                'category' => __('global.no_category'),
                            ])
                        )
                        ->merge(
                            Category::all()
                        )
                        ->map(fn(Category $category) => [
                            'key' => $category->id ?: 0,
                            'value' => $category->category,
                            'selected' => $selected->contains($category->id ?: 0),
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
     * @return JsonResourceCollection
     */
    public function tree(CategoryRequest $request): JsonResourceCollection
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

        return JsonResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
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
     * @return JsonResourceCollection
     */
    public function list(CategoryRequest $request): JsonResourceCollection
    {
        $filter = $request->input('filter');

        return JsonResource::collection(
            Category::query()
                ->when(!is_null($filter), fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
                ->paginate(config('global.number_of_results'), [
                    'id',
                    'category as name',
                    'rank',
                ])
        )
            ->meta([
                'route' => '/categories/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_category'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to' => [
                            'path' => '/categories/0',
                        ],
                    ],
                ],
            ]);
    }
}
