<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Category;
use EvolutionCMS\Models\SiteTmplvar;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\TvRequest;
use Team64j\LaravelManagerApi\Http\Resources\CategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\TvResource;
use Team64j\LaravelManagerApi\Layouts\TvLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class TvController extends Controller
{
    use PaginationTrait;

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
     * @param TvLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(TvRequest $request, TvLayout $layout): AnonymousResourceCollection
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

        return TvResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                        'title' => Lang::get('global.tmplvars'),
                        'icon' => $layout->getIcon(),
                        'pagination' => $this->pagination($result),
                    ] + ($result->isEmpty() ? ['message' => Lang::get('global.no_results')] : []),
            ]);
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
     * @param string $id
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function show(TvRequest $request, string $id, TvLayout $layout): TvResource
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

        $model->setAttribute(
            'properties',
            json_encode($model->properties, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $model->setAttribute('templates', $model->templates->pluck('id'));
        $model->setAttribute('roles', $model->roles->pluck('id'));
        $model->setAttribute('permissions', $model->tmplvarAccess->pluck('documentgroup'));

        if ($request->has('display')) {
            $model->display = $request->string('display')->toString();
        }

        $data = $model->withoutRelations();

        return TvResource::make($data)
            ->additional([
                'layout' => $layout->default($model),
                'meta' => [
                    'title' => $model->name ?? Lang::get('global.new_tmplvars'),
                    'icon' => $layout->getIcon(),
                ],
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
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function store(TvRequest $request, TvLayout $layout): TvResource
    {
        /** @var SiteTmplvar $model */
        $model = SiteTmplvar::query()->create($request->all());

        return $this->show($request, (string) $model->getKey(), $layout);
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
     * @param string $id
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function update(TvRequest $request, string $id, TvLayout $layout): TvResource
    {
        SiteTmplvar::query()->findOrFail($id)->update($request->all());

        return $this->show($request, $id, $layout);
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
     * @param string $id
     * @param TvLayout $layout
     *
     * @return TvResource
     */
    public function destroy(TvRequest $request, string $id, TvLayout $layout): TvResource
    {
        SiteTmplvar::query()->findOrFail($id)->delete();

        return $this->show($request, $id, $layout);
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
     * @return AnonymousResourceCollection
     */
    public function list(TvRequest $request): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = SiteTmplvar::withoutLocked()
            ->where(fn($query) => $filter ? $query->where('name', 'like', '%' . $filter . '%') : null)
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'), [
                'id',
                'name',
                'caption as description',
                'description as intro',
                'locked',
                'category',
            ]);

        return TvResource::collection($result->items())
            ->additional([
                'meta' => [
                    'route' => '/tvs/:id',
                    'pagination' => $this->pagination($result),
                    'prepend' => [
                        [
                            'name' => Lang::get('global.new_tmplvars'),
                            'icon' => 'fa fa-plus-circle',
                            'to' => [
                                'path' => '/tvs/new',
                            ],
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

        return TvResource::collection($result->items())
            ->additional([
                'layout' => $layout->sort(),
                'meta' => [
                    'title' => Lang::get('global.template_tv_edit_title'),
                    'icon' => $layout->getIconSort(),
                ],
                'pagination' => $this->pagination($result),
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
     * @return AnonymousResourceCollection
     */
    public function display(TvRequest $request)
    {
        return TvResource::make(
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
     * @return AnonymousResourceCollection
     */
    public function tree(TvRequest $request): AnonymousResourceCollection
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

            return TvResource::collection($result)
                ->additional([
                    'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
                ]);
        }

        if ($showFromCategory) {
            /** @var LengthAwarePaginator $result */
            $result = SiteTmplvar::withoutLocked()
                ->with('category')
                ->select($fields)
                ->where('category', $category)->orderBy('name')
                ->paginate(Config::get('global.number_of_results'))
                ->appends($request->all());

            return TvResource::collection($result->map(fn(SiteTmplvar $item) => $item->setHidden(['category'])))
                ->additional([
                    'meta' => [
                        'pagination' => $this->pagination($result),
                    ],
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

        return CategoryResource::collection($result)
            ->additional([
                'meta' => $result->isEmpty() ? ['message' => Lang::get('global.no_results')] : [],
            ]);
    }
}
