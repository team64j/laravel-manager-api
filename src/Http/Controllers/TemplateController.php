<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\TemplateRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Http\Resources\TemplateResource;
use Team64j\LaravelManagerApi\Layouts\TemplateLayout;
use Team64j\LaravelManagerApi\Models\Category;
use Team64j\LaravelManagerApi\Models\SiteTemplate;
use Team64j\LaravelManagerApi\Models\SiteTmplvar;
use Team64j\LaravelManagerApi\Models\SiteTmplvarTemplate;

class TemplateController extends Controller
{
    public function __construct(protected TemplateLayout $layout) {}

    #[OA\Get(
        path: '/templates',
        summary: 'Получение списка шаблонов с пагинацией и фильтрацией',
        security: [['Api' => []]],
        tags: ['Templates'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'templatename', in: 'query', schema: new OA\Schema(type: 'string')),
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
    public function index(TemplateRequest $request): JsonResourceCollection
    {
        $category = $request->input('category', -1);
        $name = $request->input('templatename');
        $dir = $request->input('dir', 'asc');
        $order = $request->input('order', 'category');
        $fields = ['id', 'templatename', 'templatealias', 'description', 'category', 'locked'];
        $groupBy = $request->input('groupBy');

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
            ->when($name, fn($query) => $query->where('templatename', 'like', '%' . $name . '%'))
            ->when($category >= 0, fn($query) => $query->where('category', $category))
            ->orderBy($order, $dir)
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        $viewPath = resource_path('views');
        $viewRelativePath = str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], $viewPath);

        $callbackItem = function (SiteTemplate $model) use ($viewPath, $viewRelativePath) {
            $file = '/' . $model->templatealias . '.blade.php';
            if (file_exists($viewPath . $file)) {
                $model->setAttribute(
                    'file.help',
                    __('global.template_assigned_blade_file') . '<br/>' . $viewRelativePath . $file
                );
            }

            return $model->withoutRelations();
        };

        if ($groupBy == 'category') {
            $result->setCollection(
                $result
                    ->getCollection()
                    ->groupBy('category')
                    ->map(fn($group) => [
                        'id'   => $group->first()->category,
                        'name' => $group->first()->getRelation('category')->category ?? __('global.no_category'),
                        'data' => $group->map($callbackItem),
                    ])
                    ->values()
            );
        } else {
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map($callbackItem)
            );
        }

        return JsonResource::collection($result)
            ->layout($this->layout->list())
            ->meta(
                [
                    'title' => $this->layout->titleList(),
                    'icon'  => $this->layout->iconList(),
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
    }

    #[OA\Get(
        path: '/templates/{id}',
        summary: 'Чтение шаблона',
        security: [['Api' => []]],
        tags: ['Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(int $id): JsonResource
    {
        /** @var SiteTemplate $model */
        $model = SiteTemplate::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
        }

        return TemplateResource::make($model)
            ->layout($this->layout->default($model));
    }

    #[OA\Post(
        path: '/templates',
        summary: 'Создание нового шаблона',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function store(TemplateRequest $request): JsonResource
    {
        /** @var SiteTemplate $model */
        $model = SiteTemplate::query()->create($request->input('attributes'));

        $model->tvs()->sync($request->collect('tvs'));

        $bladeFile = current(config('view.paths')) . '/' . $model->templatealias . '.blade.php';

        if (($request->input('createbladefile') || File::exists($bladeFile)) && $model->templatealias) {
            File::put($bladeFile, $model->content);
        }

        return TemplateResource::make($model)
            ->layout($this->layout->default($model));
    }

    #[OA\Put(
        path: '/templates/{id}',
        summary: 'Обновление шаблона',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function update(TemplateRequest $request, int $id): JsonResource
    {
        /** @var SiteTemplate $model */
        $model = SiteTemplate::query()->findOrFail($id);

        $model->update($request->input('attributes'));

        $model->tvs()->sync($request->collect('tvs'));

        $bladeFile = current(config('view.paths')) . '/' . $model->templatealias . '.blade.php';

        if (($request->input('createbladefile') || File::exists($bladeFile)) && $model->templatealias) {
            File::put($bladeFile, $model->content);
        }

        return TemplateResource::make($model)
            ->layout($this->layout->default($model));
    }

    #[OA\Delete(
        path: '/templates/{id}',
        summary: 'Удаление шаблона',
        security: [['Api' => []]],
        tags: ['Templates'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function destroy(int $id): Response
    {
        //        $model = SiteTemplate::query()->findOrFail($id);
        //        $model->delete();
        //
        //        $bladeFile = current(config('view.paths')) . '/' . $model->templatealias . '.blade.php';
        //
        //        if (file_exists($bladeFile)) {
        //            unlink($bladeFile);
        //        }

        return response()->noContent();
    }

    #[OA\Get(
        path: '/templates/list',
        summary: 'Получение списка шаблонов с пагинацией для меню',
        security: [['Api' => []]],
        tags: ['Templates'],
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
    public function list(TemplateRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');

        return JsonResource::collection(
            SiteTemplate::withoutLocked()
                ->where(fn($query) => $filter ? $query->where('templatename', 'like', '%' . $filter . '%') : null)
                ->orderBy('templatename')
                ->paginate(config('global.number_of_results'), [
                    'id',
                    'templatename as name',
                    'templatealias as alias',
                    'description',
                    'locked',
                    'category',
                ])
                ->appends($request->all())
        )
            ->meta([
                'route'   => '/templates/:id',
                'prepend' => [
                    [
                        'name' => __('global.new_template'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to'   => [
                            'path' => '/templates/0',
                        ],
                    ],
                ],
            ]);
    }

    #[OA\Get(
        path: '/templates/{id}/tvs',
        summary: 'Получение списка TV параметров с пагинацией для шаблона',
        security: [['Api' => []]],
        tags: ['Templates'],
        parameters: [
            new OA\Parameter(name: 'filter', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', default: 'attach')),
            new OA\Parameter(name: 'dir', in: 'query', schema: new OA\Schema(type: 'string', default: 'asc')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function tvs(TemplateRequest $request, string $template): JsonResourceCollection
    {
        $filter = $request->input('filter');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'caption', 'description', 'category', 'rank'];

        if (!in_array($order, $fields)) {
            $order = $fields[0];
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator $result */
        $result = SiteTmplvar::query()
            ->select($fields)
            ->with('category')
            ->when($filter, fn($q) => $q->where('name', 'like', '%' . $filter . '%'))
            ->when($request->has('name'), fn($q) => $q->where('name', 'like', '%' . $request->input('name') . '%'))
            ->when($order != 'attach', fn($q) => $q->orderBy($order, $dir))
            ->when(
                $request->has('attach'),
                fn($q) => $q
                    ->when(
                        $request->boolean('attach'),
                        fn(Builder $q) => $q->whereHas('tmplvarTemplate', fn($q) => $q->where('templateid', $template)),
                        fn(Builder $q) => $q->whereNotIn(
                            'id',
                            SiteTmplvarTemplate::query()->select('tmplvarid')->where('templateid', $template)
                        )
                    )
            )
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->groupBy('category')
                    ->map(fn(Collection $category) => [
                        'id'   => $category->first()->category,
                        'name' => $category->first()->getRelation('category')->category ??
                            __('global.no_category'),
                        'data' => $category/*->map(function (SiteTmplvar $item) {
                        return $item->setAttribute(
                            'attach',
                            Checkbox::make()->setModel('tvs')->setValue($item->id)
                        )
                            ->withoutRelations();
                    })*/,
                    ])
                    ->values()
            )
        )
            ->meta(
                $result->isEmpty() ? ['message' => __('global.tmplvars_novars')] : []
            );
    }

    #[OA\Get(
        path: '/templates/select',
        summary: 'Получение списка шаблонов для выбора',
        security: [['Api' => []]],
        tags: ['Templates'],
        parameters: [
            new OA\Parameter(name: 'selected', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function select(TemplateRequest $request): JsonResourceCollection
    {
        $selected = $request->collect('selected');

        return JsonResource::collection(
            collect()
                ->add([
                    'key'      => 0,
                    'value'    => 'blank (0)',
                    'selected' => $selected->contains(0),
                ])
                ->merge(
                    SiteTemplate::with('category')
                        ->select(['id', 'templatename', 'category'])
                        ->get()
                        ->groupBy('category')
                        ->map(fn(Collection $group) => [
                            'id'   => $group->first()->category,
                            'name' => $group->first()->getRelation('category')->category ??
                                __('global.no_category'),
                            'data' => $group->map(fn(SiteTemplate $item) => [
                                'key'      => $item->getKey(),
                                'value'    => $item->templatename . ' (' . $item->getKey() . ')',
                                'selected' => $selected->contains($item->getKey()),
                            ]),
                        ])
                        ->values()
                )
        );
    }

    #[OA\Get(
        path: '/templates/tree',
        summary: 'Получение списка шаблонов с пагинацией для древовидного меню',
        security: [['Api' => []]],
        tags: ['Templates'],
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
    public function tree(TemplateRequest $request): JsonResourceCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'templatename', 'category', 'locked'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteTemplate::withoutLocked()
                ->select($fields)
                ->where('templatename', 'like', '%' . $filter . '%')
                ->orderBy('templatename')
                ->get()
                ->map(fn(SiteTemplate $model) => $model->setHidden(['category']));

            return JsonResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteTemplate::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('templatename')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return JsonResource::collection(
                $result->setCollection(
                    $result
                        ->getCollection()
                        ->map(fn(SiteTemplate $model) => [
                            'id'         => $model->getKey(),
                            'title'      => $model->templatename,
                            'attributes' => $model,
                        ])
                )
            );
        }

        $result = Category::query()
            ->whereHas('templates')
            ->get();

        if (SiteTemplate::query()->where('category', 0)->exists()) {
            $result->add(new Category());
        }

        $result = $result
            ->map(function (Category $model) use ($request, $settings) {
                $data = [
                    'id'       => $model->getKey() ?? 0,
                    'title'    => $model->category ?? __('global.no_category'),
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
