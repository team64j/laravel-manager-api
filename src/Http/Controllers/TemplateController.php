<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTemplate;
use EvolutionCMS\Models\SiteTmplvar;
use EvolutionCMS\Models\SiteTmplvarTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\TemplateRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\TemplateLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;
use Team64j\LaravelManagerComponents\Checkbox;

class TemplateController extends Controller
{
    use PaginationTrait;

    /**
     * @param TemplateLayout $layout
     */
    public function __construct(protected TemplateLayout $layout)
    {
    }

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
     * @param TemplateRequest $request
     *
     * @return ResourceCollection
     */
    public function index(TemplateRequest $request): ResourceCollection
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
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $viewPath = resource_path('views');
        $viewRelativePath = str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], $viewPath);

        $callbackItem = function (SiteTemplate $item) use ($viewPath, $viewRelativePath) {
            $file = '/' . $item->templatealias . '.blade.php';
            if (file_exists($viewPath . $file)) {
                $item->setAttribute(
                    'file.help',
                    Lang::get('global.template_assigned_blade_file') . '<br/>' . $viewRelativePath . $file
                );
            }

            return $item->withoutRelations();
        };

        if ($groupBy == 'category') {
            $callbackGroup = function ($group) use ($callbackItem) {
                return [
                    'id' => $group->first()->category,
                    'name' => $group->first()->getRelation('category')->category ?? Lang::get('global.no_category'),
                    'data' => $group->map($callbackItem),
                ];
            };

            $data = $result->groupBy('category')
                ->map($callbackGroup)
                ->values();
        } else {
            $data = $result->map($callbackItem);
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
     *
     * @return JsonResource
     */
    public function show(TemplateRequest $request, string $id): JsonResource
    {
        /** @var SiteTemplate $model */
        $model = SiteTemplate::query()->findOrNew($id);

        $model->setAttribute('createbladefile', 0);
        $model->setAttribute('tvs', $model->tvs->pluck('id'));

        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';

        if (($request->input('createbladefile') || file_exists($bladeFile)) && $model->templatealias) {
            $model->setAttribute('content', file_get_contents($bladeFile));
        }

        return JsonResource::make($model->withoutRelations())
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->templatename),
                'icon' => $this->layout->icon(),
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
     *
     * @return JsonResource
     */
    public function store(TemplateRequest $request): JsonResource
    {
        /** @var SiteTemplate $model */
        $model = SiteTemplate::query()->create($request->validated());

        $tvsTemplates = $request->input('tvs', []);
        foreach ($tvsTemplates as &$tvsTemplate) {
            $tvsTemplate = [
                'tmplvarid' => $tvsTemplate,
                'templateid' => $model->getKey(),
            ];
        }

        SiteTmplvarTemplate::query()->upsert($tvsTemplates, 'tmplvarid');

        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';

        if (($request->input('createbladefile') || file_exists($bladeFile)) && $model->templatealias) {
            file_put_contents($bladeFile, $model->content);
        }

        return $this->show($request, (string) $model->getKey());
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
     *
     * @return JsonResource
     */
    public function update(TemplateRequest $request, string $id): JsonResource
    {
        $model = SiteTemplate::query()->findOrFail($id);

        $model->update($request->validated());

        SiteTmplvarTemplate::query()
            ->where('templateid', $model->getKey())
            ->delete();

        $tvsTemplates = $request->input('tvs', []);
        foreach ($tvsTemplates as &$tvsTemplate) {
            $tvsTemplate = [
                'tmplvarid' => $tvsTemplate,
                'templateid' => $model->getKey(),
            ];
        }

        SiteTmplvarTemplate::query()->upsert($tvsTemplates, 'tmplvarid');

        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';

        if (($request->input('createbladefile') || file_exists($bladeFile)) && $model->templatealias) {
            file_put_contents($bladeFile, $model->content);
        }

        return $this->show($request, (string) $model->getKey());
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
//        $model = SiteTemplate::query()->findOrFail($id);
//        $model->delete();
//
//        $bladeFile = current(Config::get('view.paths')) . '/' . $model->templatealias . '.blade.php';
//
//        if (file_exists($bladeFile)) {
//            unlink($bladeFile);
//        }

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
     * @return ResourceCollection
     */
    public function list(TemplateRequest $request): ResourceCollection
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

        return JsonResource::collection([
            'data' => $result->items(),
            'meta' => [
                'route' => '/templates/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => Lang::get('global.new_template'),
                        'icon' => 'fa fa-plus-circle',
                        'to' => [
                            'path' => '/templates/new',
                        ],
                    ],
                ],
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
     *
     * @return ResourceCollection
     */
    public function tvs(TemplateRequest $request, string $template): ResourceCollection
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
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        return JsonResource::collection(
            $result->groupBy('category')
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
                ->values()
        )
            ->meta(
                [
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => Lang::get('global.tmplvars_novars')] : [])
            );
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
     * @return ResourceCollection
     */
    public function select(TemplateRequest $request): ResourceCollection
    {
        return JsonResource::collection(
            Collection::make()
                ->add([
                    'key' => 0,
                    'value' => 'blank (0)',
                    'selected' => 0 == $request->integer('selected'),
                ])
                ->merge(
                    SiteTemplate::with('category')
                        ->select(['id', 'templatename', 'category'])
                        ->get()
                        ->groupBy('category')
                        ->map(fn($group) => [
                            'id' => $group->first()->category,
                            'name' => $group->first()->getRelation('category')->category ??
                                Lang::get('global.no_category'),
                            'data' => $group->map(fn($item) => [
                                'key' => $item->getKey(),
                                'value' => $item->templatename . ' (' . $item->getKey() . ')',
                                'selected' => $item->getKey() == $request->integer('selected'),
                            ]),
                        ])
                        ->values()
                )
        );
    }

    /**
     * @OA\Get(
     *     path="/templates/tree",
     *     summary="Получение списка шаблонов с пагинацией для древовидного меню",
     *     tags={"Templates"},
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
     * @param TemplateRequest $request
     *
     * @return ResourceCollection
     */
    public function tree(TemplateRequest $request): ResourceCollection
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
                ->map(fn(SiteTemplate $item) => $item->setHidden(['category']));

            return JsonResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteTemplate::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('templatename')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return JsonResource::collection(
                $result->map(fn(SiteTemplate $item) => $item->setHidden(['category']))
            )
                ->meta([
                    'pagination' => $this->pagination($result),
                ]);
        }

        $result = Category::query()
            ->whereHas('templates')
            ->get();

        if (SiteTemplate::query()->where('category', 0)->exists()) {
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
