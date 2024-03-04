<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\DocumentgroupName;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\DocumentRequest;
use Team64j\LaravelManagerApi\Http\Resources\DocumentResource;
use Team64j\LaravelManagerApi\Layouts\DocumentLayout;
use Team64j\LaravelManagerApi\Support\Url;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class DocumentController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/document",
     *     summary="Получение списка документов с пагинацией и фильтрацией по основным полям",
     *     tags={"Document"},
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
     * @param DocumentRequest $request
     *
     * @return AnonymousResourceCollection
     * @throws ValidationException
     */
    public function index(DocumentRequest $request): AnonymousResourceCollection
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
            $request->integer('limit', Config::get('global.number_of_results')),
            Config::get('global.number_of_results')
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
                'label' => Lang::get('global.' . $lang),
            ];
        }

        $columns = $columns->values();

        $result = SiteContent::query()
            ->orderBy($order, $dir)
            ->where($request->only($fields))
            ->paginate($limit, $fields)
            ->appends($request->all());

        return DocumentResource::collection($result->items())
            ->additional([
                'meta' => [
                    'columns' => $columns,
                    'pagination' => $this->pagination($result),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/document/{id}",
     *     summary="Чтение документа",
     *     tags={"Document"},
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
     * @param DocumentRequest $request
     * @param string $document
     * @param DocumentLayout $layout
     *
     * @return DocumentResource
     */
    public function show(DocumentRequest $request, string $document, DocumentLayout $layout): DocumentResource
    {
        /** @var SiteContent $document */
        $document = SiteContent::query()->findOrNew($document);

        if ($request->has('template')) {
            $document->template = $request->input('template');
        }

        if ($request->has('parent')) {
            $document->parent = $request->input('parent');
        }

        if ($request->has('type')) {
            $document->type = $request->input('type');
        }

        $document->setAttribute(
            'tvs',
            $document->getTvs()->keyBy('name')->map(fn($tv) => $tv['value'])
        );

        if (Config::get('global.use_udperms')) {
            /** @var Collection $groups */
            $groups = $document->documentGroups;

            $document->setAttribute(
                'is_document_group',
                $groups->isEmpty()
            );

            $document->setAttribute(
                'document_groups',
                $groups->map(
                    fn(DocumentgroupName $group) => $group->getKey()
                )
            );
        }

        $route = Url::getRouteById($document->getKey());

        return DocumentResource::make($document->withoutRelations())
            ->additional([
                'layout' => $layout->default($document, $route['url'] ?? ''),
                'meta' => [
                    'icon' => $layout->getIcon(),
                    'title' => $document->pagetitle ?? Lang::get('global.new_resource'),
                    'url' => $route['url'] ?? '',
                ],
            ]);
    }

    /**
     * @OA\Post(
     *     path="/document",
     *     summary="Создание нового документа",
     *     tags={"Document"},
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
     * @param DocumentRequest $request
     * @param DocumentLayout $layout
     *
     * @return DocumentResource
     */
    public function store(DocumentRequest $request, DocumentLayout $layout): DocumentResource
    {
        /** @var SiteContent $document */
        $document = SiteContent::query()->create($request->all());

        return $this->show($request, $document->getKey(), $layout);
    }

    /**
     * @OA\Patch(
     *     path="/document/{id}",
     *     summary="Обновление документа",
     *     tags={"Document"},
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
     * @param DocumentRequest $request
     * @param string $id
     * @param DocumentLayout $layout
     *
     * @return DocumentResource
     */
    public function update(DocumentRequest $request, string $id, DocumentLayout $layout): DocumentResource
    {
        /** @var SiteContent $document */
        $document = SiteContent::query()->findOrFail($id);
        $document->update($request->all());

        $tvs = $document->getTvs()->keyBy('name');
        foreach ($request->input('tvs', []) as $key => $value) {
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

                if ($tv['value'] != $value) {
                    if ($value != '' && !is_null($value)) {
                        // insert tv value
                    } else {
                        // delete tv value
                    }
                }
            }
        }

        return $this->show($request, $id, $layout);
    }

    /**
     * @OA\Delete(
     *     path="/document/{id}",
     *     summary="Удаление документа",
     *     tags={"Document"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DocumentRequest $request
     * @param string $id
     *
     * @return Response
     */
    public function destroy(DocumentRequest $request, string $id): Response
    {
        SiteContent::query()->findOrFail($id)->delete();

        return response()->noContent();
    }

    /**
     * @OA\Get(
     *     path="/document/tree",
     *     summary="Получение списка документов с пагинацией для древовидного меню",
     *     tags={"Document"},
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
     * @param DocumentRequest $request
     *
     * @return DocumentResource
     */
    public function tree(DocumentRequest $request)
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

            return DocumentResource::make($data);
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
                    ->paginate(Config::get('global.number_of_results'), $fields, 'settings[page]', $page)
                    ->appends($request->all()),
                fn($q) => $q->get($fields)
            );

        if ($result->isEmpty()) {
            $data = [null];
            $meta = ['message' => Lang::get('global.no_results')];
        } else {
            $data = $result->map(function (SiteContent $item) use ($request, $settings) {
                $title = $item->pagetitle;

                if (!empty($settings['keyTitle']) && $item->{$settings['keyTitle']} != null) {
                    $title = $item->{$settings['keyTitle']};
                }

                $data = [
                    'id' => $item->getKey(),
                    'alias' => $item->alias,
                    'type' => $item->type,
                    'title' => $title,
                    'template' => $item->template,
                    'menutitle' => $item->menutitle,
                    'searchable' => $item->searchable,
                    'menuindex' => $item->menuindex,
                    'published' => $item->published,
                    'cacheable' => $item->cacheable,
                    'isfolder' => $item->isfolder,
                    'richtext' => $item->richtext,
                    'deleted' => $item->deleted,
                ];

                if (!$item->hidemenu) {
                    $data['selected'] = true;
                }

                if (!$item->published) {
                    $data['unpublished'] = true;
                }

                if ($item->isfolder && !$item->hide_from_tree) {
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

                return $data;
            });

            $meta = $parent ? ['pagination' => $this->pagination($result)] : [];
        }

        return DocumentResource::make($data)
            ->additional([
                'meta' => $meta,
            ]);
    }

    /**
     * @OA\Get(
     *     path="/document/parents/{id}",
     *     summary="Получение списка родителей для документа",
     *     tags={"Document"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DocumentRequest $request
     * @param int $id
     *
     * @return DocumentResource
     */
    public function parents(DocumentRequest $request, int $id): DocumentResource
    {
        return DocumentResource::make(Url::getParentsById($id));
    }

    /**
     * @OA\Get(
     *     path="/document/parents/{parent}/{id}",
     *     summary="Получение данных при смене родителя",
     *     tags={"Document"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DocumentRequest $request
     * @param int $parent
     * @param int $id
     *
     * @return DocumentResource
     */
    public function setParent(DocumentRequest $request, int $parent, int $id): DocumentResource
    {
        if ($parent == $id) {
            abort(422, Lang::get('global.illegal_parent_self'));
        } else {
            $parents = Url::getParentsById($id, true);

            if (isset($parents[$parent])) {
                abort(422, Lang::get('global.illegal_parent_child'));
            }
        }

        if ($id > 0) {
            /** @var SiteContent $result */
            $result = SiteContent::query()->find($id);

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

        return DocumentResource::make($data);
    }
}
