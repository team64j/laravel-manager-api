<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
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

    /**
     * @OA\Get(
     *     path="/templates",
     *     summary="Получение списка шаблонов с пагинацией и фильтрацией",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="templatename", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="category")),
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

        /** @var LengthAwarePaginator $result */
        $result = SiteTemplate::withoutLocked()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($query) => $query->where('templatename', 'like', '%' . $filter . '%'))
            ->when($filterName, fn($query) => $query->where('templatename', 'like', '%' . $filterName . '%'))
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $viewPath = Config::get('view.app');
        $viewRelativePath = str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], resource_path('views'));

        return TemplateResource::collection([
            'data' => [
                'data' => $result->groupBy('category')
                    ->map(fn($category) => [
                        'id' => $category->first()->category,
                        'name' => $category->first()->getRelation('category')->category ??
                            Lang::get('global.no_category'),
                        'data' => $category->map(function (SiteTemplate $item) use ($viewPath, $viewRelativePath) {
                            $file = '/' . $item->templatealias . '.blade.php';
                            if (file_exists($viewPath . $file)) {
                                $item->setAttribute(
                                    'file.help',
                                    Lang::get('global.template_assigned_blade_file') . '<br/>' . $viewRelativePath .
                                    $file
                                );
                            }

                            return $item->withoutRelations();
                        }),
                    ])
                    ->values(),
                'pagination' => $this->pagination($result),
                'filters' => [
                    'name' => true,
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
     * @OA\Get(
     *     path="/templates/{id}",
     *     summary="Чтение шаблона",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
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
     * @OA\Post(
     *     path="/templates",
     *     summary="Создание нового шаблона",
     *     tags={"Templates"},
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

        return $this->show($request, (string) $template->getKey(), $layout);
    }

    /**
     * @OA\Put(
     *     path="/templates/{id}",
     *     summary="Обновление шаблона",
     *     tags={"Templates"},
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

        return $this->show($request, (string) $template->getKey(), $layout);
    }

    /**
     * @OA\Delete(
     *     path="/templates/{id}",
     *     summary="Удаление шаблона",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
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
     * @OA\Get(
     *     path="/templates/list",
     *     summary="Получение списка шаблонов с пагинацией для меню",
     *     tags={"Templates"},
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
     * @param TemplateRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function list(TemplateRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteTemplate::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('templatename', 'like', '%' . $filter . '%') : null)
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
                ],
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
     * @OA\Get(
     *     path="/templates/{id}/tvs",
     *     summary="Получение списка TV парметров с пагинацией для шаблона",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="attach")),
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

        /** @var LengthAwarePaginator $result */
        $result = SiteTmplvar::query()
            ->select($fields)
            ->with('category')
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

        return TemplateResource::collection([
            'data' => [
                'data' => $result->groupBy('category')
                    ->map(fn($category) => [
                        'id' => $category->first()->category,
                        'name' => $category->first()->getRelation('category')->category ??
                            Lang::get('global.no_category'),
                        'data' => $category->map(function (SiteTmplvar $item) {
                            return $item->setAttribute(
                                'attach',
                                Checkbox::make('tvs')->setValue($item->id)
                            )
                                ->withoutRelations();
                        }),
                    ])
                    ->values(),
                'pagination' => $this->pagination($result),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/templates/select",
     *     summary="Получение списка шаблонов для выбора",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="selected", in="query", @OA\Schema(type="integer")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
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
     * @OA\Get(
     *     path="/templates/tree/{category}",
     *     summary="Получение списка шаблонов с пагинацией для древовидного меню",
     *     tags={"Templates"},
     *     security={{"Api":{}}},
     *     parameters={
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
     * @param TemplateRequest $request
     * @param int $category
     *
     * @return AnonymousResourceCollection
     */
    public function tree(TemplateRequest $request, int $category): AnonymousResourceCollection
    {
        $data = [];
        $filter = $request->input('filter');
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
                        'id' => $item->getKey(),
                        'name' => $item->category,
                        'folder' => true,
                    ];

                    if (in_array($item->getKey(), $opened, true)) {
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
