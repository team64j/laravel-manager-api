<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\UserRole;
use Illuminate\Support\Facades\Config;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\RoleUserRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\RoleUserLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class RoleUserController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/roles/users",
     *     summary="Получение списка ролей для юзеров",
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
     * @param RoleUserRequest $request
     * @param RoleUserLayout $layout
     *
     * @return ResourceCollection
     */
    public function index(RoleUserRequest $request, RoleUserLayout $layout): ResourceCollection
    {
        $result = UserRole::query()
            ->when(
                $request->has('name'),
                fn($query) => $query->where('name', 'like', '%' . $request->input('name') . '%')
            )
            ->orderBy('id')
            ->paginate(Config::get('global.number_of_results'));

        return JsonResource::collection($result->items())
            ->layout($layout->list())
            ->meta([
                'title' => $layout->titleList(),
                'icon' => $layout->iconList(),
                'pagination' => $this->pagination($result),
            ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/users/{id}",
     *     summary="Получение роли для юзеров",
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
     * @param RoleUserRequest $request
     * @param string $id
     * @param RoleUserLayout $layout
     *
     * @return JsonResource
     */
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
                'icon' => $layout->icon(),
            ]);
    }
}
