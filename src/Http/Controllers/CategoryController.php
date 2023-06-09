<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelManagerApi\Http\Requests\CategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\TemplateResource;
use Team64j\LaravelManagerApi\Layouts\CategoryLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class CategoryController extends Controller
{
    use PaginationTrait;

    protected string $route = 'categories';

    /**
     * @var array
     */
    protected array $routes = [
        [
            'method' => 'get',
            'uri' => 'sort',
            'action' => [self::class, 'sort'],
        ],
        [
            'method' => 'get',
            'uri' => 'select',
            'action' => [self::class, 'select'],
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
     * @return AnonymousResourceCollection
     */
    public function index(CategoryRequest $request, CategoryLayout $layout): AnonymousResourceCollection
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

        $data = Collection::make([
            'data' => Collection::make(),
            'pagination' => $this->pagination($result),
            'filters' => [
                'category' => true,
            ],
        ]);

        /** @var Category $item */
        foreach ($result->items() as $item) {
            if (!$data['data']->has(0)) {
                $data['data'][0] = [
                    'id' => 0,
                    'data' => Collection::make(),
                ];
            }

            $item->setAttribute('#', [
                'component' => 'HelpIcon',
                'attrs' => [
                    'icon' => 'fa fa-object-group fa-fw',
                    'noOpacity' => true,
                    'fit' => true,
                ],
            ]);

            $data['data'][0]['data']->add($item->withoutRelations());
        }

        $data['data'] = $data['data']->values();

        return CategoryResource::collection([
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
     * @return CategoryResource
     */
    public function store(CategoryRequest $request): CategoryResource
    {
        $category = Category::query()->create($request->validated());

        return new CategoryResource($category);
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
     * @param string $category
     * @param CategoryLayout $layout
     *
     * @return CategoryResource
     */
    public function show(CategoryRequest $request, string $category, CategoryLayout $layout): CategoryResource
    {
        $category = Category::query()->findOrNew($category);

        return CategoryResource::make($category)
            ->additional([
                'layout' => $layout->default($category),
                'meta' => [
                    'tab' => $layout->titleDefault($category),
                ],
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
     * @param Category $category
     *
     * @return CategoryResource
     */
    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
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
     * @param Category $category
     *
     * @return Response
     */
    public function destroy(CategoryRequest $request, Category $category): Response
    {
        $category->delete();

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
     * @return AnonymousResourceCollection
     */
    public function sort(CategoryRequest $request, CategoryLayout $layout): AnonymousResourceCollection
    {
        return CategoryResource::collection([
            'data' => [
                'data' => Category::query()->orderBy('rank')->get(),
                'draggable' => true,
            ],
        ])
            ->additional([
                'layout' => $layout->sort(),
                'meta' => [
                    'tab' => $layout->titleSort(),
                ],
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
     * @return AnonymousResourceCollection
     */
    public function select(CategoryRequest $request): AnonymousResourceCollection
    {
        $selected = $request->integer('selected');

        return TemplateResource::collection(
            Collection::make()
                ->add([
                    'key' => (string)$request->input('itemNew', 'newcategory'),
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
     * @return AnonymousResourceCollection
     */
    public function tree(CategoryRequest $request): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
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
            ->when($filter, fn($query) => $query->where('category', 'like', '%' . $filter . '%'))
            ->orderByRaw('upper(' . $order . ') ' . $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $data['data'] = $result->items();
        $data['pagination'] = $this->pagination($result);

        return CategoryResource::collection([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/categories/list",
     *     summary="Получение списка категорий с пагинацией для меню",
     *     tags={"Category"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="integer")),
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
     * @return AnonymousResourceCollection
     */
    public function list(CategoryRequest $request): AnonymousResourceCollection
    {
        $filter = $request->input('filter');

        $result = Category::query()
            ->where(fn($query) => $filter ? $query->where('category', 'like', '%' . $filter . '%') : null)
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'category as name',
                'rank',
            ]);

        return CategoryResource::collection([
            'data' => [
                'data' => $result->items(),
                'pagination' => $this->pagination($result),
                'route' => 'Category',
            ],
        ]);
    }
}
