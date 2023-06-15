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
use Team64j\LaravelEvolution\Models\SiteTemplate;
use Team64j\LaravelEvolution\Models\SiteTmplvar;
use Team64j\LaravelEvolution\Models\SiteTmplvarTemplate;
use Team64j\LaravelManagerApi\Components\Checkbox;
use Team64j\LaravelManagerApi\Http\Requests\TemplateRequest;
use Team64j\LaravelManagerApi\Http\Resources\TemplateResource;
use Team64j\LaravelManagerApi\Layouts\TemplateLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class TemplateController extends Controller
{
    use PaginationTrait;

    protected string $route = 'templates';

    /**
     * @return array
     */
    protected array $routes = [
        [
            'method' => 'get',
            'uri' => '{id}/tvs',
            'action' => [self::class, 'tvs'],
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
     * @param TemplateRequest $request
     * @param TemplateLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(TemplateRequest $request, TemplateLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->input('filter');
        $filterName = $request->input('templatename');
        $dir = $request->input('dir', 'asc');
        $order = $request->input('order', 'category');
        $fields = ['id', 'templatename', 'templatealias', 'description', 'category', 'locked'];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = SiteTemplate::query()
            ->select($fields)
            ->with('categories')
            ->when($filter, fn($query) => $query->where('templatename', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('templatename', 'like', '%' . $filterName . '%'))
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $data = Collection::make();

        $viewPath = Config::get('view.app');
        $viewRelativePath = str_replace(base_path(), '', resource_path('views'));

        /** @var SiteTemplate $item */
        foreach ($result->items() as $item) {
            if (!$data->has($item->category)) {
                if ($item->category) {
                    $data[$item->category] = [
                        'id' => $item->category,
                        'name' => $item->categories->category,
                        'data' => Collection::make(),
                    ];
                } else {
                    $data[0] = [
                        'id' => 0,
                        'name' => Lang::get('global.no_category'),
                        'data' => Collection::make(),
                    ];
                }
            }

            $item->setAttribute('#', [
                'component' => 'EvoHelpIcon',
                'attrs' => [
                    'icon' => $item->id == Config::get('global.default_template') ? 'fa fa-home fa-fw text-blue-500'
                        : 'fa fa-newspaper fa-fw',
                    'iconInner' => $item->locked ? 'fa fa-lock text-xs' : '',
                    'noOpacity' => true,
                    'fit' => true,
                    'data' => $item->locked ? Lang::get('global.locked') : '',
                ],
            ]);

            $item->setAttribute('category.name', $data[$item->category]['name']);

            $file = '/' . $item->templatealias . '.blade.php';

            if (file_exists($viewPath . $file)) {
                $item->setAttribute(
                    'file',
                    [
                        'component' => 'EvoHelpIcon',
                        'attrs' => [
                            'icon' => 'fa-fw far fa-file-code',
                            'noOpacity' => true,
                            'fit' => true,
                            'data' => Lang::get('global.template_assigned_blade_file') . '<br/>' . $viewRelativePath .
                                $file,
                        ],
                    ]
                );
            }

            $data[$item->category]['data']->add($item->withoutRelations());
        }

        return TemplateResource::collection([
            'data' => [
                'data' => $data->values(),
                'pagination' => $this->pagination($result),
                'filters' => [
                    'templatename' => true,
                ],
            ],
        ])
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'tab' => $layout->titleList(),
                ],
            ]);
    }

    /**
     * @param TemplateRequest $request
     * @param string $id
     * @param TemplateLayout $layout
     *
     * @return TemplateResource
     */
    public function show(TemplateRequest $request, string $id, TemplateLayout $layout): TemplateResource
    {
        /** @var SiteTemplate $template */
        $template = SiteTemplate::query()->findOrNew($id);

        $template->setAttribute('createbladefile', 0);

        $template->setAttribute('tvs', $template->tvs->pluck('id'));

        return TemplateResource::make($template->withoutRelations())
            ->additional([
                'layout' => $layout->default($template),
                'meta' => [
                    'tab' => $layout->titleDefault($template),
                ],
            ]);
    }

    /**
     * @param TemplateRequest $request
     * @param TemplateLayout $layout
     *
     * @return TemplateResource
     */
    public function store(TemplateRequest $request, TemplateLayout $layout): TemplateResource
    {
        /** @var SiteTemplate $template */
        $template = SiteTemplate::query()->create($request->validated());

        $tvsTemplates = $request->input('tvs', []);
        foreach ($tvsTemplates as &$tvsTemplate) {
            $tvsTemplate = [
                'tmplvarid' => $tvsTemplate,
                'templateid' => $template->getKey(),
            ];
        }

        SiteTmplvarTemplate::query()->upsert($tvsTemplates, 'tmplvarid');

        return $this->show($request, (string)$template->getKey(), $layout);
    }

    /**
     * @param TemplateRequest $request
     * @param string $id
     * @param TemplateLayout $layout
     *
     * @return TemplateResource
     */
    public function update(TemplateRequest $request, string $id, TemplateLayout $layout): TemplateResource
    {
        $template = SiteTemplate::query()->findOrFail($id);

        $template->update($request->validated());

        SiteTmplvarTemplate::query()
            ->where('templateid', $template->getKey())
            ->delete();

        $tvsTemplates = $request->input('tvs', []);
        foreach ($tvsTemplates as &$tvsTemplate) {
            $tvsTemplate = [
                'tmplvarid' => $tvsTemplate,
                'templateid' => $template->getKey(),
            ];
        }

        SiteTmplvarTemplate::query()->upsert($tvsTemplates, 'tmplvarid');

        return $this->show($request, (string)$template->getKey(), $layout);
    }

    /**
     * @param TemplateRequest $request
     * @param string $id
     *
     * @return Response
     */
    public function destroy(TemplateRequest $request, string $id): Response
    {
        SiteTemplate::query()->findOrFail($id)->delete();

        return response()->noContent();
    }

    /**
     * @param TemplateRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(TemplateRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteTemplate::query()
            ->where(fn($query) => $filter ? $query->where('templatename', 'like', '%' . $filter . '%') : null)
            ->whereIn('locked', Auth::user()->attributes->role == 1 ? [0, 1] : [0])
            ->orderBy('templatename')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'templatename as name',
                'templatealias as alias',
                'description',
                'locked',
                'category',
            ])
            ->appends($request->all());

        $data = array_merge(
            [
                [
                    'name' => Lang::get('global.new_template'),
                    'icon' => 'fa fa-plus-circle',
                    'click' => [
                        'name' => 'Template',
                        'params' => [
                            'id' => 'new',
                        ],
                    ],
                ]
            ],
            $result->items()
        );

        return TemplateResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
                'route' => 'Template',
            ],
        ]);
    }

    /**
     * @param TemplateRequest $request
     * @param string $template
     * @param TemplateLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function tvs(TemplateRequest $request, string $template, TemplateLayout $layout): AnonymousResourceCollection
    {
        $tvs = SiteTemplate::query()
            ->findOrNew($template)
            ->tvs
            ->pluck('id')
            ->toArray();

        $filter = $request->input('filter');
        $order = $request->input('order', 'attach');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'caption', 'description', 'category', 'rank'];
        $orders = ['attach', 'id', 'name'];

        if (!in_array($order, $orders)) {
            $order = $orders[0];
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = SiteTmplvar::query()
            ->select($fields)
            ->with('categories')
            ->when($filter, fn($q) => $q->where('name', 'like', '%' . $filter . '%'))
            ->when(
                $order == 'attach',
                fn($q) => $q->orderByRaw(
                    'FIELD(id, "' . implode('", "', $tvs) . '") ' . ($dir == 'asc' ? 'desc' : 'asc')
                )
            )
            ->when($order != 'attach', fn($q) => $q->orderBy($order, $dir))
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $data = Collection::make();

        /** @var SiteTmplvar $item */
        foreach ($result->items() as $item) {
            if (!$data->has($item->category)) {
                if ($item->category) {
                    $data[$item->category] = [
                        'id' => $item->category,
                        'name' => $item->categories->category,
                        'data' => Collection::make(),
                    ];
                } else {
                    $data[0] = [
                        'id' => 0,
                        'name' => Lang::get('global.no_category'),
                        'data' => Collection::make(),
                    ];
                }
            }

            $item->setAttribute('category.name', $data[$item->category]['name']);

            $item->setAttribute('attach', Checkbox::make('tvs')->setValue($item->id));

            $data[$item->category]['data']->add($item->withoutRelations());
        }

        return TemplateResource::collection([
            'data' => [
                'data' => $data->values(),
                'pagination' => $this->pagination($result),
            ],
        ]);
    }

    /**
     * @param TemplateRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function select(TemplateRequest $request): AnonymousResourceCollection
    {
        $selected = $request->integer('selected');

        $data = [];

        $data['blank'] = [
            'key' => 0,
            'value' => '(blank)',
        ];

        /** @var SiteTemplate $item */
        foreach (SiteTemplate::all() as $item) {
            if (!isset($data[$item->category])) {
                if ($item->category) {
                    $data[$item->category] = [
                        'id' => $item->category,
                        'name' => $item->categories->category,
                        'data' => [],
                    ];
                } else {
                    $data[$item->category] = [
                        'id' => $item->category,
                        'name' => Lang::get('global.no_category'),
                        'data' => [],
                    ];
                }
            }

            $option = [
                'key' => $item->getKey(),
                'value' => $item->templatename . ' (' . $item->getKey() . ')',
            ];

            if ($item->getKey() == $selected) {
                $option['selected'] = true;
            }

            $data[$item->category]['data'][] = $option;
        }

        return TemplateResource::collection(
            array_values($data)
        );
    }

    /**
     * @param TemplateRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function tree(TemplateRequest $request): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
        $category = $request->integer('parent', -1);
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];

        $fields = ['id', 'templatename', 'templatealias', 'description', 'category', 'locked'];

        if ($category >= 0) {
            $result = SiteTemplate::query()
                ->select($fields)
                ->where('category', $category)
                ->when($filter, fn($query) => $query->where('templatename', 'like', '%' . $filter . '%'))
                ->orderBy('templatename')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            $data['data'] = $result->items();
            $data['pagination'] = $this->pagination($result);
        } else {
            $collection = Collection::make();

            $result = SiteTemplate::query()
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
                ->whereHas('templates')
                ->get()
                ->map(function (Category $item) use ($request, $opened) {
                    $data = [
                        'id' => $item->id,
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array((int)$item->id, $opened, true)) {
                        $result = $item->templates()
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

        return TemplateResource::collection([
            'data' => $data,
        ]);
    }
}
