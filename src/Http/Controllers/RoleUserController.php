<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Config;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\UserRole;
use Team64j\LaravelManagerApi\Http\Requests\RoleUserRequest;
use Team64j\LaravelManagerApi\Http\Resources\RoleUserResource;
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
     * @return AnonymousResourceCollection
     */
    public function index(RoleUserRequest $request, RoleUserLayout $layout): AnonymousResourceCollection
    {
        $filter = $request->get('filter');

        $result = UserRole::query()
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(Config::get('global.number_of_results'));

        return RoleUserResource::collection($result->items())
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'tab' => $layout->titleList(),
                    'pagination' => $this->pagination($result),
                ],
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
     * @param string $roleUser
     * @param RoleUserLayout $layout
     *
     * @return RoleUserResource
     */
    public function show(RoleUserRequest $request, string $roleUser, RoleUserLayout $layout): RoleUserResource
    {
        /** @var UserRole $roleUser */
        $roleUser = UserRole::query()->findOrNew($roleUser);

        if (!$roleUser->getKey()) {
            $roleUser->setRawAttributes([
                'name' => '',
            ]);
        }

        return RoleUserResource::make([])
            ->additional([
                'layout' => $layout->default($roleUser),
                'meta' => [
                    'tab' => $layout->titleDefault($roleUser),
                ],
            ]);
    }
}
