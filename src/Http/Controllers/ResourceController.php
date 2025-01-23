<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvarContentvalue;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ResourceRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceResource;
use Team64j\LaravelManagerApi\Layouts\ResourceLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class ResourceController extends Controller
{
    use PaginationTrait;

    public function __construct(protected ResourceLayout $layout)
    {
    }

    /**
     * @OA\Get(
     *     path="/resource",
     *     summary="Получение списка ресурсов с пагинацией и фильтрацией по основным полям",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *         @OA\Parameter (name="limit", in="query", @OA\Schema(type="integer")),
     *         @OA\Parameter (name="columns", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="fields", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="additional", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ResourceRequest $request
     *
     * @return ApiCollection
     * @throws ValidationException
     */
    public function index(ResourceRequest $request): ApiCollection
    {
        $fillable = ['id', ...(new SiteContent())->getFillable()];

        $defaultFields = [
            'id',
            'parent',
            'isfolder',
            'pagetitle',
            'longtitle',
            'menutitle',
            'description',
            'menuindex',
            'hidemenu',
            'hide_from_tree',
            'type',
            'published',
            'deleted',
            'editedon',
            'createdon',
        ];

        $limit = min(
            $request->integer('limit', config('global.number_of_results')),
            config('global.number_of_results')
        );
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $columns = $request->string('columns')->explode(',');

        $fields = $request->string('fields', implode(',', $defaultFields))->explode(',');
        $additional = $request->string('additional')->explode(',');

        if ($additional->count()) {
            $fields = $fields->merge($additional);
        }

        $fields = $fields
            ->map(fn($i) => trim($i))
            ->intersect($fillable)
            ->filter()
            ->values()
            ->unique()
            ->toArray();

        $this->getValidationFactory()
            ->make(['fields' => $fields], ['fields' => 'required'])
            ->validate();

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        foreach ($columns as $key => $column) {
            if (!in_array($column, $fields)) {
                unset($columns[$key]);
                continue;
            }

            $lang = $column;

            if ($lang == 'longtitle') {
                $lang = 'long_title';
            }

            $columns[$key] = [
                'name' => $column,
                'label' => __('global.' . $lang),
            ];
        }

        $columns = $columns->values();

        $result = SiteContent::withTrashed()
            ->orderBy($order, $dir)
            ->where($request->only($fields))
            ->paginate($limit, $fields)
            ->appends($request->all());

        return ApiResource::collection($result->items())
            ->meta([
                'columns' => $columns,
                'pagination' => $this->pagination($result),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/resource/{id}",
     *     summary="Чтение ресурса",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="template", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="type", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ResourceRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function show(ResourceRequest $request, int $id): ApiResource
    {
        /** @var SiteContent $model */
        $model = SiteContent::withTrashed()->findOrNew($id);

        if ($request->has('template')) {
            $model->template = $request->input('template');
        }

        if ($request->has('parent')) {
            $model->parent = $request->input('parent');
        }

        if ($request->has('type')) {
            $model->type = $request->input('type');
        }

        return ResourceResource::make($model)
            ->layout($this->layout->default($model));
    }

    /**
     * @OA\Post(
     *     path="/resource",
     *     summary="Создание нового ресурса",
     *     tags={"Resource"},
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
     * @param ResourceRequest $request
     *
     * @return ApiResource
     */
    public function store(ResourceRequest $request): ApiResource
    {
        /** @var SiteContent $model */
        $model = SiteContent::query()->create($request->all());

        return ResourceResource::make($model)
            ->layout($this->layout->default($model));
    }

    /**
     * @OA\Patch(
     *     path="/resource/{id}",
     *     summary="Обновление ресурса",
     *     tags={"Resource"},
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
     * @param ResourceRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function update(ResourceRequest $request, int $id): ApiResource
    {
        /** @var SiteContent $model */
        $model = SiteContent::withTrashed()->findOrFail($id);
        $model->update($request->input('attributes'));

        $tvs = $model->getTvs()->keyBy('name');

        foreach ($request->collect('tvs') as $key => $value) {
            if ($tvs->has($key)) {
                $tv = $tvs->get($key);

                switch ($tv['type']) {
                    case 'radio':
                    case 'checkbox':
                    case 'listbox-multiple':
                        if (is_array($tv['value'])) {
                            $tv['value'] = implode('||', $tv['value']);
                        }

                        if (is_array($value)) {
                            $value = implode('||', $value);
                        }

                        break;
                }

                if ($value != '' && !is_null($value) && $tv['value'] != $value) {
                    SiteTmplvarContentvalue::query()
                        ->updateOrInsert([
                            'tmplvarid' => $tv['id'],
                            'contentid' => $model->getKey(),
                        ], [
                            'value' => $value,
                        ]);
                } else {
                    SiteTmplvarContentvalue::query()
                        ->where('tmplvarid', $tv['id'])
                        ->where('contentid', $model->getKey())
                        ->delete();
                }
            }
        }

        return ResourceResource::make($model->refresh())
            ->layout($this->layout->default($model));
    }

    /**
     * @OA\Delete(
     *     path="/resource/{id}",
     *     summary="Удаление ресурса",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param int $id
     *
     * @return ResourceResource
     */
    public function destroy(int $id)
    {
        $model = SiteContent::withTrashed()->findOrFail($id);

        $model->update([
            'deleted' => !$model->deleted,
        ]);

        return ResourceResource::make($model)
            ->layout($this->layout->default($model));
    }

    /**
     * @OA\Get(
     *     path="/resource/tree",
     *     summary="Получение списка ресурсов с пагинацией для древовидного меню",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="int", default="0")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *         @OA\Parameter (name="opened", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="settings", in="query", @OA\Schema(type="string")),
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
     *
     * @param ResourceRequest $request
     *
     * @return ApiCollection
     */
    public function tree(ResourceRequest $request)
    {
        $settings = $request->collect('settings')->toArray();
        $settings['opened'] = array_map('intval', $settings['opened'] ?? []);
        $parent = $settings['parent'] ?? -1;
        $page = $settings['page'] ?? 1;
        $order = $settings['order'] ?? 'menuindex';
        $dir = $settings['dir'] ?? 'asc';

        if ($parent < 0) {
            $data = [];

            foreach ([0] as $id) {
                $request->query->set('settings', array_merge($settings, ['parent' => $id]));
                $data[] = [
                    'id' => $id,
                    'title' => 'root',
                    'category' => true,
                    'data' => $this->tree($request),
                    'templates' => [
                        'title' => 'root',
                    ],
                    'contextMenu' => null,
                ];
            }

            return ApiResource::collection($data);
        }

        $fields = [
            'id',
            'parent',
            'pagetitle',
            'longtitle',
            'menutitle',
            'isfolder',
            'alias',
            'type',
            'menuindex',
            'template',
            'hide_from_tree',
            'hidemenu',
            'published',
            'deleted',
            'richtext',
            'searchable',
            'cacheable',
            'createdon',
            'editedon',
            'publishedon',
        ];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var LengthAwarePaginator|SiteContent $result */
        $result = SiteContent::withTrashed()
            ->with(['documentGroups'])
            ->where('parent', $parent)
            ->orderBy($order, $dir)
            ->when(
                $parent,
                fn($q) => $q
                    ->paginate(config('global.number_of_results'), $fields, 'settings[page]', $page)
                    ->appends($request->all()),
                fn($q) => $q->get($fields)
            );

        if ($result->isEmpty()) {
            $data = [];
            $meta = ['message' => __('global.no_results')];
        } else {
            $data = $result->map(function (SiteContent $item) use ($request, $settings) {
                $data = [
                    'id' => $item->id,
                    'title' => $item->{$settings['keyTitle']} ?? $item->pagetitle,
                    'attributes' => $item,
                ];

                if ($item->isfolder) {
                    if ($item->hide_from_tree) {
                        $data['data'] = null;
                    } else {
                        $data['data'] = [];

                        if (in_array($item->getKey(), $settings['opened'], true)) {
                            $request->query->set(
                                'settings',
                                [
                                    'parent' => $item->getKey(),
                                    'page' => null,
                                ] + $settings
                            );

                            $result = $this->tree($request);

                            $data['data'] = $result->resource ?? [];
                            $data['meta'] = $result->additional['meta'] ?? [];
                        }
                    }
                }

                return $data;
            });

            $meta = $parent ? ['pagination' => $this->pagination($result)] : [];
        }

        return ApiResource::collection($data)
            ->meta($meta);
    }

    /**
     * @OA\Get(
     *     path="/resource/parents/{id}",
     *     summary="Получение списка родителей для ресурса",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ResourceRequest $request
     * @param int $id
     *
     * @return ApiResource
     */
    public function parents(ResourceRequest $request, int $id): ApiResource
    {
        return ApiResource::make(url()->getParentsById($id));
    }

    /**
     * @OA\Get(
     *     path="/resource/parents/{parent}/{id}",
     *     summary="Получение данных при смене родителя",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ResourceRequest $request
     * @param int $parent
     * @param int $id
     *
     * @return ApiResource
     */
    public function setParent(ResourceRequest $request, int $parent, int $id): ApiResource
    {
        if ($parent == $id) {
            abort(422, __('global.illegal_parent_self'));
        } else {
            $parents = url()->getParentsById($id, true);

            if (isset($parents[$parent])) {
                abort(422, __('global.illegal_parent_child'));
            }
        }

        if ($id > 0) {
            /** @var SiteContent $result */
            $result = SiteContent::withTrashed()->find($id);

            $data = [
                'id' => $result->getKey(),
                'title' => $result->pagetitle,
                'parent' => $result->parent,
            ];
        } else {
            $data = [
                'id' => 0,
                'title' => 'root',
                'parent' => null,
            ];
        }

        return ApiResource::make($data);
    }
}
