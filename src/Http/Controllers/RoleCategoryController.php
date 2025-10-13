<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\RoleCategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\RoleCategoryLayout;
use Team64j\LaravelManagerApi\Models\PermissionsGroups;

class RoleCategoryController extends Controller
{
    #[OA\Get(
        path: '/roles/categories',
        summary: 'Получение списка категорий для прав доступа',
        security: [['Api' => []]],
        tags: ['Users'],
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
    public function index(RoleCategoryRequest $request, RoleCategoryLayout $layout): JsonResourceCollection
    {
        $filter = $request->get('filter');

        $result = PermissionsGroups::query()
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(function (PermissionsGroups $item) {
                        $item->name = __('global.' . $item->lang_key);

                        return $item;
                    })
            )
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->titleList(),
                'icon'  => $layout->iconList(),
            ]);
    }

    #[OA\Get(
        path: '/roles/categories/{id}',
        summary: 'Получение категории для прав доступа',
        security: [['Api' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function show(
        RoleCategoryRequest $request,
        string $id,
        RoleCategoryLayout $layout
    ): JsonResource {
        /** @var PermissionsGroups $model */
        $model = PermissionsGroups::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setRawAttributes([
                'name' => '',
            ]);
        }

        return JsonResource::make([])
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title(
                    trans()->has('global.' . $model->lang_key) ? __('global.' . $model->lang_key) : null
                ),
                'icon'  => $layout->icon(),
            ]);
    }
}
