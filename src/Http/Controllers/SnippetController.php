<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\SnippetRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteSnippet;

class SnippetController extends Controller
{
    public function __construct(protected SnippetLayout $layout) {}

    #[OA\Get(
        path: '/snippets',
        summary: 'Получение списка сниппетов с пагинацией и фильтрацией',
        security: [['Api' => []]],
        tags: ['Snippets'],
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
    public function index(SnippetRequest $request): JsonResourceCollection
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
        path: '/snippets',
        summary: 'Создание нового сниппета',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Snippets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function store(SnippetRequest $request): JsonResource
    {
        $data = $request->validated();

        $data['snippet'] = str($data['snippet'] ?? '')->replaceFirst('<?php', '');

        $model = SiteSnippet::query()->create($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Get(
        path: '/snippets/{id}',
        summary: 'Чтение сниппета',
        security: [['Api' => []]],
        tags: ['Snippets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(SnippetRequest $request, int $id): JsonResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
            $model->setAttribute('category', 0);
        }

        $model->setAttribute('snippet', "<?php\r\n" . $model->snippet);
        $model->setAttribute('analyze', (int) !$model->exists);

        return JsonResource::make($model)
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->name),
                'icon'  => $this->layout->icon(),
            ]);
    }

    #[OA\Put(
        path: '/snippets/{id}',
        summary: 'Обновление сниппета',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Snippets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function update(SnippetRequest $request, int $id): JsonResource
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);

        $data = $request->validated();

        $data['snippet'] = str($data['snippet'] ?? '')->replaceFirst('<?php', '');

        $model->update($data);

        return $this->show($request, $model->getKey());
    }

    #[OA\Delete(
        path: '/snippets/{id}',
        summary: 'Удаление сниппета',
        security: [['Api' => []]],
        tags: ['Snippets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function destroy(SnippetRequest $request, int $id): Response
    {
        /** @var SiteSnippet $model */
        $model = SiteSnippet::query()->findOrFail($id);

        $model->delete();

        return response()->noContent();
    }

    #[OA\Get(
        path: '/snippets/list',
        summary: 'Получение списка сниппетов с пагинацией для меню',
        security: [['Api' => []]],
        tags: ['Snippets'],
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
    public function list(SnippetRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteSnippet::withoutLocked()
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
                'route'   => '/snippets/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_snippet'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to'   => [
                            'path' => '/snippets/0',
                        ],
                    ],
                ],
            ]);
    }

    #[OA\Get(
        path: '/snippets/tree',
        summary: 'Получение списка сниппетов с пагинацией для древовидного меню',
        security: [['Api' => []]],
        tags: ['Snippets'],
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
    public function tree(SnippetRequest $request): JsonResourceCollection
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
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteSnippet::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return JsonResource::collection(
                $result->setCollection(
                    $result
                        ->getCollection()
                        ->map(fn(SiteSnippet $item) => [
                            'id'         => $item->id,
                            'title'      => $item->name,
                            'attributes' => $item,
                        ])
                )
            );
        }

        $result = Category::query()
            ->whereHas('snippets')
            ->get();

        if (SiteSnippet::withoutLocked()->where('category', 0)->exists()) {
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
