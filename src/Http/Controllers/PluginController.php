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
use Team64j\LaravelEvolution\Models\SitePlugin;
use Team64j\LaravelEvolution\Models\SystemEventname;
use Team64j\LaravelManagerApi\Components\HelpIcon;
use Team64j\LaravelManagerApi\Http\Requests\PluginRequest;
use Team64j\LaravelManagerApi\Http\Resources\PluginResource;
use Team64j\LaravelManagerApi\Layouts\PluginLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class PluginController extends Controller
{
    use PaginationTrait;

    /**
     * @param PluginRequest $request
     * @param PluginLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(PluginRequest $request, PluginLayout $layout): AnonymousResourceCollection
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

        $result = SitePlugin::query()
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
                'name' => true
            ],
        ]);

        /** @var SitePlugin $item */
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

            $item->setAttribute(
                '#',
                HelpIcon::make($item->locked ? Lang::get('global.locked') : '', 'fa fa-plug fa-fw')
                    ->setInnerIcon($item->locked ? 'fa fa-lock text-xs' : '')
                    ->isOpacity(false)
                    ->isFit()
            );

            $item->setAttribute('category.name', $data['data'][$item->category]['name']);
            $item->setAttribute('description.html', $item->description);

            $data['data'][$item->category]['data']->add($item->withoutRelations());
        }

        $data['data'] = $data['data']->values();

        return PluginResource::collection([
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
     * @param PluginRequest $request
     *
     * @return PluginResource
     */
    public function store(PluginRequest $request): PluginResource
    {
        $plugin = SitePlugin::query()->create($request->validated());

        return new PluginResource($plugin);
    }

    /**
     * @param PluginRequest $request
     * @param string $plugin
     * @param PluginLayout $layout
     *
     * @return PluginResource
     */
    public function show(PluginRequest $request, string $plugin, PluginLayout $layout): PluginResource
    {
        $plugin = SitePlugin::query()->findOrNew($plugin);

        return PluginResource::make($plugin)
            ->additional([
                'layout' => $layout->default($plugin),
                'meta' => [
                    'tab' => $layout->titleDefault($plugin),
                ],
            ]);
    }

    /**
     * @param PluginRequest $request
     * @param SitePlugin $plugin
     *
     * @return PluginResource
     */
    public function update(PluginRequest $request, SitePlugin $plugin): PluginResource
    {
        $plugin->update($request->validated());

        return new PluginResource($plugin);
    }

    /**
     * @param PluginRequest $request
     * @param SitePlugin $plugin
     *
     * @return Response
     */
    public function destroy(PluginRequest $request, SitePlugin $plugin): Response
    {
        $plugin->delete();

        return response()->noContent();
    }

    /**
     * @param PluginRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(PluginRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SitePlugin::query()
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
                    'name' => Lang::get('global.new_plugin'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Plugin',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ],
            ],
            $result->items()
        );

        return PluginResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Plugin',
            ],
        ]);
    }

    /**
     * @param PluginRequest $request
     * @param PluginLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function sort(PluginRequest $request, PluginLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');

        return PluginResource::collection([
            'data' => [
                'data' => SystemEventname::query()
                    ->with(
                        'plugins',
                        fn($q) => $q
                            ->select(['id', 'name', 'disabled', 'priority'])
                            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
                            ->orderBy('pivot_priority')
                    )
                    ->whereHas('plugins')
                    ->orderBy('name')
                    ->get()
                    ->map(function (SystemEventname $item) {
                        $item->setAttribute('data', $item->plugins);
                        $item->setAttribute('draggable', true);

                        return $item->withoutRelations();
                    }),
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
     * @param PluginRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(PluginRequest $request): AnonymousResourceCollection
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
            $result = SitePlugin::query()
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

            $result = SitePlugin::query()
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
                ->whereHas('plugins')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->id,
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array((int) $item->id, $opened, true)) {
                        $result = $item->plugins()
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

        return PluginResource::collection([
            'data' => $data,
        ]);
    }
}
