<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\PermissionsGroups;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\RoleCategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\ResourceCollection;
use Team64j\LaravelManagerApi\Layouts\RoleCategoryLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class RoleCategoryController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/roles/categories",
     *     summary="Получение списка категорий для прав доступа",
     *     tags={"Users"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="filter", in="query", @OA\Schema(type="string")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param RoleCategoryRequest $request
     * @param RoleCategoryLayout $layout
     *
     * @return ResourceCollection
     */
    public function index(RoleCategoryRequest $request, RoleCategoryLayout $layout): ResourceCollection
    {
        $filter = $request->get('filter');

        $result = PermissionsGroups::query()
            ->when($filter, fn($query) => $query->where('name', 'like', '%' . $filter . '%'))
            ->orderBy('id')
            ->paginate(Config::get('global.number_of_results'));

        $data = Collection::make($result->items())
            ->map(function (PermissionsGroups $item) {
                $item->name = Lang::get('global.' . $item->lang_key);

                return $item;
            });

        return JsonResource::collection($data)
            ->additional([
                'layout' => $layout->list(),
                'meta' => [
                    'title' => $layout->titleList(),
                    'icon' => $layout->iconList(),
                    'pagination' => $this->pagination($result),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/roles/categories/{id}",
     *     summary="Получение категории для прав доступа",
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
     * @param RoleCategoryRequest $request
     * @param string $id
     * @param RoleCategoryLayout $layout
     *
     * @return JsonResource
     */
    public function show(
        RoleCategoryRequest $request,
        string $id,
        RoleCategoryLayout $layout): JsonResource
    {
        /** @var PermissionsGroups $model */
        $model = PermissionsGroups::query()->findOrNew($id);

        if (!$model->getKey()) {
            $model->setRawAttributes([
                'name' => ''
            ]);
        }

        return JsonResource::make([])
            ->additional([
                'layout' => $layout->default($model),
                'meta' => [
                    'title' => $layout->title(
                        Lang::has('global.' . $model->lang_key) ? Lang::get('global.' . $model->lang_key) : null
                    ),
                    'icon' => $layout->icon(),
                ],
            ]);
    }
}
