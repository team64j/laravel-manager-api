<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\Permissions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\RolePermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\RoleCategoryResource;
use Team64j\LaravelManagerApi\Http\Resources\RolePermissionResource;
use Team64j\LaravelManagerApi\Layouts\RolePermissionLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class RolePermissionController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/roles/permissions",
     *     summary="Получение списка прав доступа для юзеров",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param RolePermissionRequest $request
     * @param RolePermissionLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(RolePermissionRequest $request, RolePermissionLayout $layout): AnonymousResourceCollection
    {
        $data = Collection::make();
        $filter = $request->get('filter');

        $result = Permissions::query()
            ->with('groups')
            ->when($filter, fn($query) => $query->where('key', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(Config::get('global.number_of_results'));

        /** @var Permissions $item */
        foreach ($result->items() as $item) {
            if (!$data->has($item->group_id)) {
                if ($item->group_id) {
                    $data[$item->group_id] = [
                        'id' => $item->group_id,
                        'name' => Lang::get('global.' . $item->groups->lang_key),
                        'data' => Collection::make(),
                    ];
                } else {
                    $data[0] = [
                        'id' => 0,
                        'name' => Lang::get('global.no_category'),
                        'data' => Collection::make(),
                    ];
                }
            }

            $item->name = Lang::get('global.' . $item->lang_key);

            $data[$item->group_id]['data']->add($item->withoutRelations());
        }

        return RolePermissionResource::collection($data->values())
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'title' => Lang::get('global.role_management_title'),
                    'icon' => $layout->getIconList(),
                    'pagination' => $this->pagination($result),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/permissions/{id}",
     *     summary="Получение права доступа для юзеров",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param RolePermissionRequest $request
     * @param string $rolePermission
     * @param RolePermissionLayout $layout
     *
     * @return RoleCategoryResource
     */
    public function show(
        RolePermissionRequest $request,
        string $rolePermission,
        RolePermissionLayout $layout): RoleCategoryResource
    {
        /** @var Permissions $rolePermission */
        $rolePermission = Permissions::query()->findOrNew($rolePermission);

        if (!$rolePermission->getKey()) {
            $rolePermission->setRawAttributes([
                'name' => '',
            ]);
        }

        return RoleCategoryResource::make([])
            ->additional([
                'layout' => $layout->default($rolePermission),
                'meta' => [
                    'title' => Lang::has('global.' . $rolePermission->lang_key) ? Lang::get('global.' . $rolePermission->lang_key)
                        : Lang::get('global.new_permission'),
                    'icon' => $layout->getIcon(),
                ],
            ]);
    }
}
