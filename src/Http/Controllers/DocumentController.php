<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Facades\Uri;
use Team64j\LaravelEvolution\Models\DocumentgroupName;
use Team64j\LaravelEvolution\Models\SiteContent;
use Team64j\LaravelManagerApi\Http\Requests\DocumentRequest;
use Team64j\LaravelManagerApi\Http\Resources\DocumentResource;
use Team64j\LaravelManagerApi\Layouts\DocumentLayout;
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

        return DocumentResource::collection([
            'data' => [
                'data' => $result->items(),
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
            $document->getTvs()->keyBy('name')
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

        return DocumentResource::make(
            $document->withoutRelations()
        )
            ->additional([
                'layout' => $layout->default($document),
                'meta' => [
                    'tab' => $layout->titleDefault($document),
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
        $document = SiteContent::query()->create($request->validated());

        return $this->getDocument($request, $document, $layout);
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
        $document->update($request->validated());

        return $this->getDocument($request, $document, $layout);
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
     * @param DocumentRequest $request
     * @param SiteContent $document
     * @param DocumentLayout $layout
     *
     * @return DocumentResource
     */
    protected function getDocument(
        DocumentRequest $request,
        SiteContent $document,
        DocumentLayout $layout): DocumentResource
    {
        $data = array_merge(
            $document->withoutRelations()->toArray(),
            ['empty_cache' => 1],
            $this->tvs($document)
        );

        return (new DocumentResource($data))
            ->additional([
                'meta' => $this->getMeta($request, $document),
                'layout' => $layout->default($document),
            ]);
    }

    /**
     * @param DocumentRequest $request
     * @param SiteContent $document
     *
     * @return array
     */
    protected function getMeta(DocumentRequest $request, SiteContent $document): array
    {
        $route = Uri::getRouteById($document->id);

        return [
            'url' => $route['url'] ?? '',
            'tab' => [
                'title' => $document->pagetitle ?: Lang::get('global.new_resource'),
                'icon' => 'fa fa-edit',
            ],
        ];
    }

    /**
     * @param SiteContent $document
     *
     * @return array
     */
    protected function tvs(SiteContent $document): array
    {
        $tvs = $document->getTvs();
        $data = [];

        foreach ($tvs as $tv) {
            $value = $tv['value'];

            switch ($tv['type']) {
                case 'radio':
                case 'checkbox':
                case 'listbox-multiple':
                    if ($tv['elements']) {
                        if (!is_array($value)) {
                            $value = $tv['value'] == '' ? [] : explode('||', $value);
                        }
                    }

                    break;

                default:
            }

            $data[$tv['name']] = $value;
        }

        return $data;
    }

    /**
     * @OA\Get(
     *     path="/document/tree",
     *     summary="Получение списка документов с пагинацией для древовидного меню",
     *     tags={"Document"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="parent", in="query", @OA\Schema(type="integer")),
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *         @OA\Parameter (name="opened", in="query", @OA\Schema(type="string")),
     *         @OA\Parameter (name="settings", in="query", @OA\Schema(type="string")),
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
     */
    public function tree(DocumentRequest $request): AnonymousResourceCollection
    {
        $parent = $request->integer('parent');
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $opened = $request->has('opened') ? $request->string('opened')
            ->explode(',')
            ->map(fn($i) => intval($i))
            ->toArray() : [];
        $settings = $request->whenFilled('settings', fn($i) => json_decode($i, true));
        $settings['keyTitle'] = $settings['keyTitle'] ?? 'pagetitle';

        if (!empty($settings['order'])) {
            $order = $settings['order'];
        }

        if (!empty($settings['dir'])) {
            $dir = $settings['dir'];
        }

        $fields = [
            'id',
            'parent',
            'pagetitle',
            'longtitle',
            'menutitle',
            'isfolder',
            'alias',
            'template',
            'richtext',
            'menuindex',
            'hidemenu',
            'hide_from_tree',
            'type',
            'published',
            'deleted',
            'editedon',
            'createdon',
            'searchable',
            'cacheable',
        ];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $result */
        $result = SiteContent::query()
            ->select($fields)
            ->where('parent', $parent)
            ->with('documentGroups')
            ->orderBy($order, $dir)
            ->paginate(Config::get('global.number_of_results'))
            ->appends($request->all());

        $result->map(function (SiteContent $item) use ($opened, $request, $fields, $settings) {
            if (in_array($item->getKey(), $opened, true)) {
                $request = clone $request;
                $request->query->add([
                    'parent' => $item->getKey(),
                    'page' => 1,
                ]);
                $result = $this->tree($request);
                $item->setAttribute('data', $result['data']);
            }

            $title = in_array($settings['keyTitle'], $fields) ? $item->getAttribute($settings['keyTitle']) : '';

            if ((string) $title == '') {
                $title = $item->getAttribute('pagetitle');
            }

            return $item->setAttribute('title', $title)
                ->setAttribute('private', $item->documentGroups->isNotEmpty());
        });

        return DocumentResource::collection([
            'data' => [
                'data' => $result->items(),
                'pagination' => $this->pagination($result),
            ],
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
        return DocumentResource::make(Uri::getParentsById($id));
    }
}
