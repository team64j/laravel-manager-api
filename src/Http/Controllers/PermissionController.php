<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\PermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\PermissionGroupLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionRelationLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionResourceLayout;
use Team64j\LaravelManagerApi\Models\DocumentgroupName;
use Team64j\LaravelManagerApi\Models\MembergroupName;
use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerApi\Models\PermissionsGroups;
use Team64j\LaravelManagerApi\Models\SiteContent;
use Team64j\LaravelManagerApi\Models\User;

class PermissionController extends Controller
{
    #[OA\Get(
        path: '/permissions/groups',
        summary: 'Получение списка групп пользователей',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function groups(PermissionRequest $request, PermissionGroupLayout $layout): JsonResourceCollection
    {
        $result = MembergroupName::query()
            ->with('users')
            ->orderBy('name')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(function (MembergroupName $group) {
                        if ($group->users->count()) {
                            $users = $group
                                ->users
                                ->map(fn(User $i) => '
                            <button type="button" href="users/' . $i->getKey() .
                                    '" class="mr-1 link btn-sm max-w-20" title="' .
                                    $i->username . '">
                                <span class="truncate">' . $i->username . '</span>
                            </button>'
                                )
                                ->join(' ');
                        } else {
                            $users =
                                '<span class="opacity-50">' . __('global.access_permissions_no_users_in_group') .
                                '</span>';
                        }

                        return $group
                            ->withoutRelations()
                            ->setAttribute('users.html', $users);
                    })
            )
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/group/{id}',
        summary: 'Получение группы пользователей',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function group(
        PermissionRequest $request,
        string $id,
        PermissionGroupLayout $layout
    ): JsonResource {
        $model = MembergroupName::query()->findOrNew($id);

        return JsonResource::make($model)
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title($model->name),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/resources',
        summary: 'Получение групп документов',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function resources(
        PermissionRequest $request,
        PermissionResourceLayout $layout
    ): JsonResourceCollection {
        $result = DocumentgroupName::query()
            ->with('documents')
            ->when($request->has('name'), fn($q) => $q->where('name', 'like', '%' . $request->input('name') . '%'))
            ->orderBy('name')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(function (DocumentgroupName $group) {
                        if ($group->documents->count()) {
                            $documents = $group
                                ->documents
                                ->map(fn(SiteContent $i) => '
                            <button type="button" href="/resource/' . $i->getKey() .
                                    '" class="mr-1 link btn-sm w-20" title="' .
                                    e($i->pagetitle) . '">
                                <span class="truncate">' . e($i->pagetitle) . '(' . $i->getKey() . ')</span>
                            </button>'
                                )
                                ->join(' ');
                        } else {
                            $documents =
                                '<span class="opacity-50">' . __('global.access_permissions_no_resources_in_group') .
                                '</span>';
                        }

                        return $group
                            ->withoutRelations()
                            ->setAttribute('documents.html', $documents);
                    })
            )
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/resources/{id}',
        summary: 'Получение группы документов',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function resource(
        PermissionRequest $request,
        string $id,
        PermissionResourceLayout $layout
    ): JsonResource {
        $model = DocumentgroupName::query()->findOrNew($id);

        return JsonResource::make($model)
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title($model->name),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/relations',
        summary: 'Получение групп связей юзеров с документами',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function relations(
        PermissionRequest $request,
        PermissionRelationLayout $layout
    ): JsonResourceCollection {
        $result = MembergroupName::query()
            ->with('documentGroups')
            ->orderBy('name')
            ->paginate(config('global.number_of_results'));

        $documents = DocumentgroupName::query()
            ->with('documents')
            ->orderBy('name')
            ->get();

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(function (MembergroupName $group) {
                        if ($group->documentGroups->count()) {
                            $documentGroups = $group
                                ->documentGroups
                                ->map(fn(DocumentgroupName $i) => '
                            <div class="pb-1">
                                <span class="font-medium">' . $i->name . '</span> (' .
                                    ($i->pivot->context ? 'web' : 'mgr') . ')
                                <i class="fa fa-close text-rose-500"/>
                            </div>'
                                )
                                ->join(' ');
                        } else {
                            $documentGroups = '<span class="opacity-50">' . __('global.no_groups_found') . '</span>';
                        }

                        return $group
                            ->withoutRelations()
                            ->setAttribute('document_groups.html', $documentGroups);
                    })
            )
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/relations/{id}',
        summary: 'Получение группы связей юзеров с документами',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function relation(
        PermissionRequest $request,
        string $id,
        PermissionRelationLayout $layout
    ): JsonResource {
        $data = MembergroupName::query()->findOrNew($id);

        return JsonResource::make($data)
            ->layout($layout->default($data))
            ->meta([
                'title' => $layout->title($data->name),
                'icon'  => $layout->icon(),
            ]);
    }

    #[OA\Get(
        path: '/permissions/select',
        summary: 'Получение списка разрешений',
        security: [['Api' => []]],
        tags: ['Permissions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function select(PermissionRequest $request): JsonResourceCollection
    {
        $selected = $request->input('selected') ?: [];

        if ($selected && is_string($selected)) {
            $selected = explode(',', $selected);
        }

        return JsonResource::collection(
            PermissionsGroups::with('permissions')
                ->get()
                ->map(fn(PermissionsGroups $group) => [
                    'name' => __('global.' . $group->lang_key),
                    'data' => $group->permissions->map(fn(Permissions $permission) => [
                        'key'      => $permission->key,
                        'value'    => __('global.' . $permission->lang_key),
                        'selected' => in_array($permission->key, $selected, true),
                    ]),
                ])
                ->sortBy('value')
        );
    }
}
