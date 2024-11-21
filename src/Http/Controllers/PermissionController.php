<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\DocumentgroupName;
use EvolutionCMS\Models\MembergroupName;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\PermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\PermissionGroupLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionRelationLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionResourceLayout;
use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerApi\Models\PermissionsGroups;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class PermissionController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/permissions/groups",
     *     summary="Получение списка групп пользователей",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param PermissionGroupLayout $layout
     *
     * @return ResourceCollection
     */
    public function groups(PermissionRequest $request, PermissionGroupLayout $layout): ResourceCollection
    {
        $result = MembergroupName::query()
            ->with('users')
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'));

        return JsonResource::collection(
            $result
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
                            '<span class="opacity-50">' . Lang::get('global.access_permissions_no_users_in_group') .
                            '</span>';
                    }

                    return $group->withoutRelations()
                        ->setAttribute('users.html', $users);
                })
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon' => $layout->icon(),
                'pagination' => $this->pagination($result),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/group/{id}",
     *     summary="Получение групы пользователей",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionGroupLayout $layout
     *
     * @return JsonResource
     */
    public function group(
        PermissionRequest $request,
        string $id,
        PermissionGroupLayout $layout): JsonResource
    {
        $model = MembergroupName::query()->findOrNew($id);

        return JsonResource::make($model)
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title($model->name),
                'icon' => $layout->icon(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/resources",
     *     summary="Получение групп документов",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param PermissionResourceLayout $layout
     *
     * @return ResourceCollection
     */
    public function resources(
        PermissionRequest $request,
        PermissionResourceLayout $layout): ResourceCollection
    {
        $result = DocumentgroupName::query()
            ->with('documents')
            ->when($request->has('name'), fn($q) => $q->where('name', 'like', '%' . $request->input('name') . '%'))
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'));

        return JsonResource::collection(
            $result
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
                            '<span class="opacity-50">' . Lang::get('global.access_permissions_no_resources_in_group') .
                            '</span>';
                    }

                    return $group->withoutRelations()
                        ->setAttribute('documents.html', $documents);
                })
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon' => $layout->icon(),
                'pagination' => $this->pagination($result),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/resources/{id}",
     *     summary="Получение групы документов",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionResourceLayout $layout
     *
     * @return JsonResource
     */
    public function resource(
        PermissionRequest $request,
        string $id,
        PermissionResourceLayout $layout): JsonResource
    {
        $model = DocumentgroupName::query()->findOrNew($id);

        return JsonResource::make($model)
            ->layout($layout->default($model))
            ->meta([
                'title' => $layout->title($model->name),
                'icon' => $layout->icon(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/relations",
     *     summary="Получение групп связей юзеров с документами",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param PermissionRelationLayout $layout
     *
     * @return ResourceCollection
     */
    public function relations(
        PermissionRequest $request,
        PermissionRelationLayout $layout): ResourceCollection
    {
        $result = MembergroupName::query()
            ->with('documentGroups')
            ->orderBy('name')
            ->paginate(Config::get('global.number_of_results'));

        $documents = DocumentgroupName::query()
            ->with('documents')
            ->orderBy('name')
            ->get();

        return JsonResource::collection(
            $result
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
                        $documentGroups = '<span class="opacity-50">' . Lang::get('global.no_groups_found') . '</span>';
                    }

                    return $group->withoutRelations()
                        ->setAttribute('document_groups.html', $documentGroups);
                })
        )
            ->layout($layout->list())
            ->meta([
                'title' => $layout->title(),
                'icon' => $layout->icon(),
                'pagination' => $this->pagination($result),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/relations/{id}",
     *     summary="Получение групы связей юзеров с документами",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionRelationLayout $layout
     *
     * @return JsonResource
     */
    public function relation(
        PermissionRequest $request,
        string $id,
        PermissionRelationLayout $layout): JsonResource
    {
        $data = MembergroupName::query()->findOrNew($id);

        return JsonResource::make($data)
            ->layout($layout->default($data))
            ->meta([
                'title' => $layout->title($data->name),
                'icon' => $layout->icon(),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/permissions/select",
     *     summary="Получение списка разрешений",
     *     tags={"Permissions"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param PermissionRequest $request
     *
     * @return ResourceCollection
     */
    public function select(PermissionRequest $request): ResourceCollection
    {
        $selected = $request->input('selected') ?: [];

        if ($selected && is_string($selected)) {
            $selected = explode(',', $selected);
        }

        return JsonResource::collection(
            PermissionsGroups::with('permissions')
                ->get()
                ->map(fn(PermissionsGroups $group) => [
                    'name' => Lang::get('global.' . $group->lang_key),
                    'data' => $group->permissions->map(fn(Permissions $permission) => [
                        'key' => $permission->key,
                        'value' => Lang::get('global.' . $permission->lang_key),
                        'selected' => in_array($permission->key, $selected, true),
                    ]),
                ])
                ->sortBy('value')
        );
    }
}
