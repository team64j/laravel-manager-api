<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\CategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class CategoryController extends Controller
{
    use PaginationTrait;

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
     * @param CategoryLayout $layout
     *
     * @return ResourceCollection
     */
    public function index(CategoryRequest $request, CategoryLayout $layout): ResourceCollection
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

        return JsonResource::collection($result->items())
            ->meta(
                [
                    'title' => Lang::get('global.category_management'),
                    'icon' => $layout->icon(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [])
            )
            ->layout($layout->list());
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
        $model = Category::query()->create($request->validated());

        return JsonResource::make($model);
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
     * @param string $id
     * @param CategoryLayout $layout
     *
     * @return JsonResource
     */
    public function show(CategoryRequest $request, string $id, CategoryLayout $layout): JsonResource
    {
        /** @var Category $model */
        $model = Category::query()->findOrNew($id);

        return JsonResource::make($model)
            ->meta([
                'title' => $model->category ?? $layout->title(),
                'icon' => $layout->icon(),
            ])
            ->layout($layout->default($model));
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
     * @param string $id
     *
     * @return JsonResource
     */
    public function update(CategoryRequest $request, string $id): JsonResource
    {
        /** @var Category $model */
        $model = Category::query()->findOrFail($id);

        $model->update($request->validated());

        return JsonResource::make($model);
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
     * @param string $id
     *
     * @return Response
     */
    public function destroy(CategoryRequest $request, string $id): Response
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
     * @param CategoryLayout $layout
     *
     * @return ResourceCollection
     */
    public function sort(CategoryRequest $request, CategoryLayout $layout): ResourceCollection
    {
        return JsonResource::collection(Category::query()->orderBy('rank')->get())
            ->meta([
                'title' => Lang::get('global.cm_sort_categories'),
                'icon' => $layout->iconSort(),
            ])
            ->layout($layout->sort());
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
     * @return ResourceCollection
     */
    public function select(CategoryRequest $request): ResourceCollection
    {
        $selected = $request->integer('selected');

        return JsonResource::collection(
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
     * @return ResourceCollection
     */
    public function tree(CategoryRequest $request): ResourceCollection
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
     * @return ResourceCollection
     */
    public function list(CategoryRequest $request): ResourceCollection
    {
        $filter = $request->input('filter');

        $result = Category::query()
            ->when(!is_null($filter), fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'category as name',
                'rank',
            ]);

        return JsonResource::collection($result->items())
            ->meta([
                'route' => '/categories/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => Lang::get('global.new_category'),
                        'icon' => 'fa fa-plus-circle',
                        'to' => [
                            'path' => '/categories/new',
                        ],
                    ],
                ],
            ]);
    }
}
