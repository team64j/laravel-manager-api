<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\RolePermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\RolePermissionLayout;
use Team64j\LaravelManagerApi\Models\Permissions;
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
     * @return JsonResourceCollection
     */
    public function index(RolePermissionRequest $request, RolePermissionLayout $layout): JsonResourceCollection
    {
        $data = collect();
        $filter = $request->get('filter');

        $result = Permissions::query()
            ->with('groups')
            ->when($filter, fn($query) => $query->where('key', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(config('global.number_of_results'));

        /** @var Permissions $item */
        foreach ($result->items() as $item) {
            if (!$data->has($item->group_id)) {
                if ($item->group_id) {
                    $data[$item->group_id] = [
                        'id' => $item->group_id,
                        'name' => __('global.' . $item->groups->lang_key),
                        'data' => collect(),
                    ];
                } else {
                    $data[0] = [
                        'id' => 0,
                        'name' => __('global.no_category'),
                        'data' => collect(),
                    ];
                }
            }

            $item->name = __('global.' . $item->lang_key);

            $data[$item->group_id]['data']->add($item->withoutRelations());
        }

        return JsonResource::collection($data->values())
            ->layout($layout->list())
            ->meta([
                'title' => $layout->titleList(),
                'icon' => $layout->iconList(),
                'pagination' => $this->pagination($result),
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
     * @param string $id
     * @param RolePermissionLayout $layout
     *
     * @return JsonResource
     */
    public function show(
        RolePermissionRequest $request,
        string $id,
        RolePermissionLayout $layout): JsonResource
    {
        /** @var Permissions $model */
        $model = Permissions::query()->findOrNew($id);

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
                'icon' => $layout->icon(),
            ]);
    }
}
