<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTmplvar;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\TvRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\TvLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class TvController extends Controller
{
    use PaginationTrait;

    /**
     * @param TvLayout $layout
     */
    public function __construct(protected TvLayout $layout)
    {
    }

    /**
     * @OA\Get(
     *     path="/tvs",
     *     summary="Получение списка TV параметров с пагинацией и фильтрацией",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     *
     * @return ApiCollection
     */
    public function index(TvRequest $request): ApiCollection
    {
        $filter = $request->input('filter');
        $category = $request->input('category', -1);
        $filterName = $request->input('name');
        $order = $request->input('order', 'category');
        $dir = $request->input('dir', 'asc');
        $fields = ['id', 'name', 'type', 'caption', 'locked', 'category', 'rank'];
        $groupBy = $request->input('groupBy');

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator $result */
        $result = SiteTmplvar::withoutLocked()
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
                    'id' => $group->first()->category,
                    'name' => $group->first()->getRelation('category')->category ?? __('global.no_category'),
                    'data' => $group->map->withoutRelations(),
                ];
            };

            $data = $result->groupBy('category')
                ->map($callbackGroup)
                ->values();
        } else {
            $data = $result->map(fn($item) => $item->withoutRelations());
        }

        return ApiResource::collection($data)
            ->layout($this->layout->list())
            ->meta(
                [
                    'title' => $this->layout->titleList(),
                    'icon' => $this->layout->iconList(),
                    'pagination' => $this->pagination($result),
                ] + ($result->isEmpty() ? ['message' => __('global.no_results')] : [])
            );
    }

    /**
     * @OA\Get(
     *     path="/tvs/{id}",
     *     summary="Чтение TV параметра",
     *     tags={"Tvs"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param TvRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function show(TvRequest $request, int $id): ApiResource
    {
        /** @var SiteTmplvar $model */
        $model = SiteTmplvar::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setRawAttributes([
                'type' => 'text',
                'category' => 0,
                'rank' => 0,
            ]);
        }

        if ($request->has('display')) {
            $model->display = $request->string('display')->toString();
        }

        $model->properties = $model->properties ? json_encode(
            $model->properties,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ) : '[]';

        $model->setAttribute('permissions', $model->permissions->pluck('id'));
        $model->setAttribute('templates', $model->templates->pluck('id'));
        $model->setAttribute('roles', $model->roles->pluck('id'));

        $params = array_filter(explode('&', $model->display_params ?? ''));
        $data = [];

        foreach ($params as $param) {
            [$key, $value] = explode('=', $param);
            $data[$key] = $value;
        }

        $model->setAttribute('display_params_data', $data);

        return ApiResource::make($model->withoutRelations())
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $this->layout->title($model->name),
                'icon' => $this->layout->icon(),
            ]);
    }

    /**
     * @OA\Post(
     *     path="/tvs",
     *     summary="Создание нового TV параметра",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     *
     * @return ApiResource
     */
    public function store(TvRequest $request): ApiResource
    {
        $data = $request->all();

        $data['properties'] = json_decode($data['properties'] ?? '[]') ?: null;

        /** @var SiteTmplvar $model */
        $model = SiteTmplvar::query()->create($data);

        $model->permissions()->sync($data['permissions'] ?? []);
        $model->templates()->sync($data['templates'] ?? []);
        $model->roles()->sync($data['roles'] ?? []);

        return $this->show($request, $model->getKey());
    }

    /**
     * @OA\Put(
     *     path="/tvs/{id}",
     *     summary="Обновление TV параметра",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function update(TvRequest $request, int $id): ApiResource
    {
        $data = $request->all();

        $data['properties'] = json_decode($data['properties'] ?? '[]') ?: null;

        $model = SiteTmplvar::query()->findOrFail($id);
        $model->update($data);

        $model->permissions()->sync($data['permissions'] ?? []);
        $model->templates()->sync($data['templates'] ?? []);
        $model->roles()->sync($data['roles'] ?? []);

        return $this->show($request, $model->getKey());
    }

    /**
     * @OA\Delete(
     *     path="/tvs/{id}",
     *     summary="Удаление TV параметра",
     *     tags={"Tvs"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param TvRequest $request
     * @param int $id
     *
     * @return Response
     */
    public function destroy(TvRequest $request, int $id)
    {
        SiteTmplvar::query()->findOrFail($id)->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/tvs/list",
     *     summary="Получение списка TV параметров с пагинацией для меню",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     *
     * @return ApiCollection
     */
    public function list(TvRequest $request): ApiCollection
    {
        $filter = $request->get('filter');

        $result = SiteTmplvar::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->orderBy('name')
            ->paginate(config('global.number_of_results'), [
                'id',
                'name',
                'caption as description',
                'description as intro',
                'locked',
                'category',
            ]);

        return ApiResource::collection($result->items())
            ->meta([
                'route' => '/tvs/:id',
                'pagination' => $this->pagination($result),
                'prepend' => [
                    [
                        'name' => __('global.new_tmplvars'),
                        'icon' => 'fa fa-plus-circle text-green-500',
                        'to' => [
                            'path' => '/tvs/0',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/tvs/sort",
     *     summary="Получение списка TV параметров с пагинацией для сортировки",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     *
     * @return ApiCollection
     */
    public function sort(TvRequest $request): ApiCollection
    {
        $result = SiteTmplvar::query()
            ->select(['id', 'name', 'caption', 'rank'])
            ->orderBy('rank')
            ->paginate(config('global.number_of_results'));

        return ApiResource::collection($result->items())
            ->layout($this->layout->sort())
            ->meta([
                'title' => $this->layout->titleSort(),
                'icon' => $this->layout->iconSort(),
                'pagination' => $this->pagination($result)
            ]);
    }

    /**
     * @OA\Get(
     *     path="/tvs/types",
     *     summary="Получение списка типов TV параметров для выбора",
     *     tags={"Tvs"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="selected", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param TvRequest $request
     *
     * @return ApiCollection
     */
    public function types(TvRequest $request): ApiCollection
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

        return ApiResource::collection($types);
    }

    /**
     * @OA\Get(
     *     path="/tvs/display",
     *     summary="Получение списка виджетов для TV",
     *     tags={"Tvs"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="selected", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param TvRequest $request
     *
     * @return ApiCollection
     */
    public function display(TvRequest $request)
    {
        return ApiResource::make(
            array_merge(
                [
                    [
                        'key' => '',
                        'value' => '',
                    ],
                ],
                (new SiteTmplvar())->getDisplay()
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/tvs/tree",
     *     summary="Получение списка TV параметров с пагинацией для древовидного меню",
     *     tags={"Tvs"},
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
     * @param TvRequest $request
     *ф
     *
     * @return ApiCollection
     */
    public function tree(TvRequest $request): ApiCollection
    {
        $settings = $request->collect('settings');
        $category = $settings['parent'] ?? -1;
        $filter = $request->input('filter');

        $fields = ['id', 'name', 'category', 'locked'];
        $showFromCategory = $category >= 0;

        if (!is_null($filter)) {
            $result = SiteTmplvar::withoutLocked()
                ->select($fields)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('name')
                ->get()
                ->map(fn(SiteTmplvar $item) => $item->setHidden(['category']));

            return ApiResource::collection($result)
                ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteTmplvar::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(config('global.number_of_results'))
                ->appends($request->all());

            return ApiResource::collection($result->map(fn(SiteTmplvar $item) => $item->setHidden(['category'])))
                ->meta([
                    'pagination' => $this->pagination($result),
                ]);
        }

        $result = Category::query()
            ->whereHas('tvs')
            ->get();

        if (SiteTmplvar::query()->where('category', 0)->exists()) {
            $result->add(new Category());
        }

        $result = $result->map(function ($category) use ($request, $settings) {
            $data = [
                'id' => $category->getKey() ?? 0,
                'name' => $category->category ?? __('global.no_category'),
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
            ->sort(fn($a, $b) => $a['id'] == 0 ? -1 : (str($a['name'])->upper() > str($b['name'])->upper()))
            ->values();

        return ApiResource::collection($result)
            ->meta($result->isEmpty() ? ['message' => __('global.no_results')] : []);
    }
}
