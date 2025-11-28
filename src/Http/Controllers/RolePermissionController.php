<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\RolePermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\RolePermissionLayout;
use Team64j\LaravelManagerApi\Models\Permissions;

class RolePermissionController extends Controller
{
    public function __construct(protected RolePermissionLayout $layout) {}

    #[OA\Get(
        path: '/roles/permissions',
        summary: 'Получение списка прав доступа для юзеров',
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
    public function index(RolePermissionRequest $request): JsonResourceCollection
    {
        $filter = $request->get('filter');

        $result = Permissions::query()
            ->with('groups')
            ->when($filter, fn($query) => $query->where('key', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->groupBy('group_id')
                    ->map(
                        static fn($group, $key) => [
                            'id'   => $key,
                            'name' => $key
                                ? __('global.' . $group->first()->groups->lang_key)
                                : __('global.no_category'),
                            'data' => $group->map(
                                static fn($item) => $item->setAttribute('lang_key', __('global.' . $item->lang_key))
                            ),
                        ]
                    )
            )
        )
            ->layout($this->layout->list());
    }

    #[OA\Get(
        path: '/roles/permissions/{id}',
        summary: 'Получение права доступа для юзеров',
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
    public function show(RolePermissionRequest $request, string $id): JsonResource
    {
        /** @var Permissions $model */
        $model = Permissions::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setAttribute($model->getKeyName(), 0);
            $model->setAttribute('name', '');
        }

        return JsonResource::make([])
            ->layout($this->layout->default($model));
    }
}
