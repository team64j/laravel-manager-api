<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteSnippet;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\SnippetRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class SnippetController extends Controller
{
    use PaginationTrait;

    /**
     * @param SnippetLayout $layout
     */
    public function __construct(protected SnippetLayout $layout)
    {
    }

    /**
     * @OA\Get(
     *     path="/snippets",
     *     summary="Получение списка сниппетов с пагинацией и фильтрацией",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return ResourceCollection
     */
    public function index(SnippetRequest $request): ResourceCollection
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
        $result = SiteSnippet::withoutLocked()
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

        return JsonResource::collection($data)
            ->layout($this->layout->list())
            ->meta(
                [
                    'title' => $this->layout->titleList(),
                    'icon' => $this->layout->iconList(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [])
            );
    }

    /**
     * @OA\Post(
     *     path="/snippets",
     *     summary="Создание нового сниппета",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return JsonResource
     */
    public function store(SnippetRequest $request): JsonResource
    {
        $data = $request->validated();

        $data['snippet'] = Str::replaceFirst('<?php', '', $data['snippet'] ?? '');
        
        $model = SiteSnippet::query()->create($data);

        return $this->show($request, (string) $model->getKey());
    }

    /**
     * @OA\Get(
     *     path="/snippets/{id}",
     *     summary="Чтение сниппета",
     *     tags={"Snippets"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param SnippetRequest $request
     * @param string $id
     *
     * @return JsonResource
     */
    public function show(SnippetRequest $request, string $id): JsonResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrNew($id);

        $model->setAttribute('snippet', "<?php\r\n" . $model->snippet);
        $model->setAttribute('analyze', (int) !$model->exists);

        return JsonResource::make($model)
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->name),
                'icon' => $this->layout->icon(),
            ]);
    }

    /**
     * @OA\Put(
     *     path="/snippets/{id}",
     *     summary="Обновление сниппета",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     * @param string $id
     *
     * @return JsonResource
     */
    public function update(SnippetRequest $request, string $id): JsonResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);
        
        $data = $request->validated();

        $data['snippet'] = Str::replaceFirst('<?php', '', $data['snippet'] ?? '');

        $model->update($data);

        return $this->show($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/snippets/{id}",
     *     summary="Удаление сниппета",
     *     tags={"Snippets"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param SnippetRequest $request
     * @param string $id
     *
     * @return Response
     */
    public function destroy(SnippetRequest $request, string $id): Response
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/snippets/list",
     *     summary="Получение списка сниппетов с пагинацией для меню",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return ResourceCollection
     */
    public function list(SnippetRequest $request): ResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteSnippet::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('disabled', Auth::user()->isAdmin() ? [0, 1] : [0])
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);

        return JsonResource::collection($result->items())
            ->meta([
                'route' => '/snippets/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => Lang::get('global.new_snippet'),
                        'icon' => 'fa fa-plus-circle',
                        'to' => [
                            'path' => '/snippets/new',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/snippets/tree",
     *     summary="Получение списка сниппетов с пагинацией для древовидного меню",
     *     tags={"Snippets"},
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
     * @param SnippetRequest $request
     *
     * @return ResourceCollection
     */
    public function tree(SnippetRequest $request): ResourceCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked', 'disabled'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteSnippet::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SiteSnippet $item) => $item->setHidden(['category']));

            return JsonResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteSnippet::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return JsonResource::collection($result->map(fn(SiteSnippet $item) => $item->setHidden(['category'])))
                ->meta([
                    'pagination' => $this->pagination($result),
                ]);
        }

        $result = Category::query()
            ->whereHas('snippets')
            ->get();

        if (SiteSnippet::withoutLocked()->where('category', 0)->exists()) {
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

        return JsonResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []);
    }
}
