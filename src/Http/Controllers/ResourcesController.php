<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ResourcesRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\ResourcesLayout;
use Team64j\LaravelManagerApi\Models\SiteContent;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class ResourcesController extends Controller
{
    use PaginationTrait;

    public function __construct(protected ResourcesLayout $layout)
    {
    }

    /**
     * @OA\Get(
     *     path="/resources/{id}",
     *     summary="Получение списка ресурсов с пагинацией",
     *     tags={"Resource"},
     *     security={{"Api":{}}},
     *     parameters={
     *         @OA\Parameter (name="order", in="query", @OA\Schema(type="string", default="id")),
     *         @OA\Parameter (name="dir", in="query", @OA\Schema(type="string", default="asc")),
     *     },
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ResourcesRequest $request
     * @param int $id
     *
     * @return JsonResource
     */
    public function show(ResourcesRequest $request, int $id): JsonResource
    {
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');

        $fields = [
            'id',
            'parent',
            'isfolder',
            'pagetitle',
            'longtitle',
            'menutitle',
            'description',
            'menuindex',
            'hidemenu',
            'hide_from_tree',
            'type',
            'published',
            'deleted',
            'editedon',
            'createdon',
            'publishedon',
        ];

        if (!in_array($order, $fields)) {
            $order = 'id';
        }

        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $result = SiteContent::withTrashed()
            ->select($fields)
            ->where('parent', $id)
            ->orderBy($order, $dir)
            ->paginate(config('global.number_of_results'))
            ->appends($request->all());

        $model = SiteContent::withTrashed()->findOr($id, [
            'id',
            'pagetitle',
        ], fn() => new SiteContent([
            'id' => 0,
            'pagetitle' => 'root',
        ]));

        return JsonResource::make($result->items())
            ->layout($this->layout->default($model))
            ->meta([
                'title' => $model->pagetitle,
                'icon' => $this->layout->icon(),
                'pagination' => $this->pagination($result),
                'sorting' => [
                    'order' => $order,
                    'dir' => $dir,
                ],
            ]);
    }
}
