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
use Team64j\LaravelEvolution\Models\SiteTmplvar;
use Team64j\LaravelManagerApi\Http\Requests\TvRequest;
use Team64j\LaravelManagerApi\Http\Resources\TvResource;
use Team64j\LaravelManagerApi\Layouts\TvLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class TvController extends Controller
{
    use PaginationTrait;

    protected string $route = 'tvs';

    /**
     * @return array
     */
    protected array $routes = [
        [
            'method' => 'get',
            'uri' => 'sort',
            'action' => [self::class, 'sort'],
        ],
        [
            'method' => 'get',
            'uri' => 'types',
            'action' => [self::class, 'types'],
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
     * @param TvRequest $request
     * @param TvLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(TvRequest $request, TvLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');
        $filterName = $request->input('name');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'type', 'caption', 'locked', 'category', 'rank'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = SiteTmplvar::query()
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

        /** @var SiteTmplvar $item */
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
                    'icon' => 'fa fa-list-alt fa-fw',
                    'iconInner' => $item->locked ? 'fa fa-lock text-xs' : '',
                    'noOpacity' => true,
                    'fit' => true,
                    'data' => $item->locked ? Lang::get('global.locked') : '',
                ],
            ]);

            $item->setAttribute('category.name', $data['data'][$item->category]['name']);

            $data['data'][$item->category]['data']->add($item->withoutRelations());
        }

        $data['data'] = $data['data']->values();

        return TvResource::collection([
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
     * @param TvRequest $request
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function store(TvRequest $request, TvLayout $layout): TvResource
    {
        /** @var SiteTmplvar $tv */
        $tv = SiteTmplvar::query()->create($request->validated());

        $data = $tv->withoutRelations();

        return (new TvResource($data))
            ->additional([
                'meta' => [],
                'layout' => $layout->default($tv),
            ]);
    }

    /**
     * @param TvRequest $request
     * @param string $tv
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function show(TvRequest $request, string $tv, TvLayout $layout): TvResource
    {
        /** @var SiteTmplvar $tv */
        $tv = SiteTmplvar::query()->findOrNew($tv);

        if (!$tv->id) {
            $tv->setRawAttributes([
                'type' => 'text',
                'category' => 0,
                'rank' => 0,
            ]);
        }

        $data = $tv->withoutRelations();

        return (new TvResource($data))
            ->additional([
                'layout' => $layout->default($tv),
                'meta' => [
                    'tab' => $layout->titleDefault($tv),
                ],
            ]);
    }

    /**
     * @param TvRequest $request
     * @param SiteTmplvar $tv
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function update(TvRequest $request, SiteTmplvar $tv, TvLayout $layout): TvResource
    {
        $tv->update($request->validated());

        $data = $tv->withoutRelations();

        return (new TvResource($data))
            ->additional([
                'meta' => [
                    'tab' => $layout->titleDefault($tv),
                ],
                'layout' => $layout->default($tv),
            ]);
    }

    /**
     * @param TvRequest $request
     * @param SiteTmplvar $tv
     *
     * @return Response
     */
    public function destroy(TvRequest $request, SiteTmplvar $tv): Response
    {
        $tv->delete();

        return response()->noContent();
    }

    /**
     * @param TvRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(TvRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteTmplvar::query()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'caption as description',
                'description as intro',
                'locked',
                'category',
            ]);

        $data = array_merge(
            [
                [
                    'name' => Lang::get('global.new_tmplvars'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Tv',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ]
            ],
            $result->items()
        );

        return TvResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Tv',
            ],
        ]);
    }

    /**
     * @param TvRequest $request
     * @param TvLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function sort(TvRequest $request, TvLayout $layout): AnonymousResourceCollection
    {
        $result = SiteTmplvar::query()
            ->select(['id', 'name', 'caption', 'rank'])
            ->orderBy('rank')
            ->paginate(Config::get('global.number_of_results'));

        return TvResource::collection([
            'data' => [
                'pagination' => $this->pagination($result),
                'draggable' => true,
                'data' => $result->items(),
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
     * @param TvRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function types(TvRequest $request): AnonymousResourceCollection
    {
        $types = (new SiteTmplvar())->parameterTypes();
        $selected = $request->string('selected')->toString();

        foreach ($types as $key => $type) {
            foreach ($type['data'] as $k => $item) {
                if ($selected == $item['key']) {
                    $types[$key]['data'][$k]['selected'] = true;
                }
            }
        }

        return TvResource::collection($types);
    }

    /**
     * @param TvRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(TvRequest $request): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
        $category = $request->integer('parent', -1);
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];

        $fields = ['id', 'name', 'caption', 'description', 'category', 'locked'];

        if ($category >= 0) {
            $result = SiteTmplvar::query()
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

            $result = SiteTmplvar::query()
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
                ->whereHas('tvs')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->id,
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array((int) $item->id, $opened, true)) {
                        $result = $item->tvs()
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

        return TvResource::collection([
            'data' => $data,
        ]);
    }
}
