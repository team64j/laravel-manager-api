<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\RoleUserRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\RoleUserLayout;
use Team64j\LaravelManagerApi\Models\UserRole;

class RoleUserController extends Controller
{
    #[OA\Get(
        path: '/roles/users',
        summary: 'Получение списка ролей для юзеров',
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
    public function index(RoleUserRequest $request, RoleUserLayout $layout): JsonResourceCollection
    {
        $result = UserRole::query()
            ->when(
                $request->has('name'),
                fn($query) => $query->where('name', 'like', '%' . $request->input('name') . '%')
            )
            ->orderBy('id')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection($result)
            ->layout($layout->list())
            ->meta([
                'title' => $layout->titleList(),
                'icon'  => $layout->iconList(),
            ]);
    }

    #[OA\Get(
        path: '/roles/users/{id}',
        summary: 'Получение роли для юзеров',
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
    public function show(RoleUserRequest $request, string $id, RoleUserLayout $layout): JsonResource
    {
        /** @var UserRole $model */
        $model = UserRole::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setRawAttributes([
                'name' => '',
            ]);
        }

        return JsonResource::make([])
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title($model->name),
                'icon'  => $layout->icon(),
            ]);
    }
}
