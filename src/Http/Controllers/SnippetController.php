<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\Category;
use Team64j\LaravelEvolution\Models\SiteSnippet;
use Team64j\LaravelManagerApi\Http\Requests\SnippetRequest;
use Team64j\LaravelManagerApi\Http\Resources\SnippetResource;
use Team64j\LaravelManagerApi\Layouts\SnippetLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class SnippetController extends Controller
{
    use PaginationTrait;

    protected string $route = 'snippets';

    /**
     * @return array
     */
    protected array $routes = [
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
     * @param SnippetRequest $request
     * @param SnippetLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(SnippetRequest $request, SnippetLayout $layout): AnonymousResourceCollection
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

        $result = SiteSnippet::query()
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

        /** @var SiteSnippet $item */
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

            $item->setAttribute('#', [
                'component' => 'EvoHelpIcon',
                'attrs' => [
                    'icon' => 'fa fa-code fa-fw',
                    'iconInner' => $item->locked ? 'fa fa-lock text-xs' : '',
                    'noOpacity' => true,
                    'fit' => true,
                    'data' => $item->locked ? Lang::get('global.locked') : '',
                ],
            ]);

            $item->setAttribute('category.name', $data['data'][$item->category]['name']);
            $item->setAttribute('description.html', $item->description);

            $data['data'][$item->category]['data']->add($item->withoutRelations());
        }

        $data['data'] = $data['data']->values();

        return SnippetResource::collection([
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
     * @param SnippetRequest $request
     *
     * @return SnippetResource
     */
    public function store(SnippetRequest $request): SnippetResource
    {
        $snippet = SiteSnippet::query()->create($request->validated());

        return new SnippetResource($snippet);
    }

    /**
     * @param SnippetRequest $request
     * @param string $snippet
     * @param SnippetLayout $layout
     *
     * @return SnippetResource
     */
    public function show(SnippetRequest $request, string $snippet, SnippetLayout $layout): SnippetResource
    {
        $snippet = SiteSnippet::query()->findOrNew($snippet);

        return SnippetResource::make($snippet)
            ->additional([
                'layout' => $layout->default($snippet),
                'meta' => [
                    'tab' => $layout->titleDefault($snippet),
                ],
            ]);
    }

    /**
     * @param SnippetRequest $request
     * @param SiteSnippet $snippet
     *
     * @return SnippetResource
     */
    public function update(SnippetRequest $request, SiteSnippet $snippet): SnippetResource
    {
        $snippet->update($request->validated());

        return new SnippetResource($snippet);
    }

    /**
     * @param SnippetRequest $request
     * @param SiteSnippet $snippet
     *
     * @return Response
     */
    public function destroy(SnippetRequest $request, SiteSnippet $snippet): Response
    {
        $snippet->delete();

        return response()->noContent();
    }

    /**
     * @param SnippetRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(SnippetRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteSnippet::query()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->whereIn('disabled', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'description',
                'locked',
                'disabled',
                'category',
            ]);

        $data = array_merge(
            [
                [
                    'name' => Lang::get('global.new_snippet'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Snippet',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ],
            ],
            $result->items()
        );

        return SnippetResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Snippet',
            ],
        ]);
    }

    /**
     * @param SnippetRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(SnippetRequest $request): AnonymousResourceCollection
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
            $result = SiteSnippet::query()
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

            $result = SiteSnippet::query()
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
                ->whereHas('snippets')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->id,
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array((int) $item->id, $opened, true)) {
                        $result = $item->snippets()
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

        return SnippetResource::collection([
            'data' => $data,
        ]);
    }
}
