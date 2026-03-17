<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Team64j\LaravelManagerApi\Http\Requests\PermissionGroupRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\PermissionGroupLayout;
use Team64j\LaravelManagerApi\Models\PermissionsGroups;

class PermissionGroupController extends Controller
{
    public function index(PermissionGroupRequest $request, PermissionGroupLayout $layout)
    {
        $result = PermissionsGroups::query()
            ->orderBy('id')
            ->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(function (PermissionsGroups $item) {
                        $item->name = __('global.' . $item->lang_key);

                        return $item;
                    })
            )
        )
            ->layout($layout->list());
    }

    public function show(PermissionGroupRequest $request, int $id, PermissionGroupLayout $layout)
    {
        $model = PermissionsGroups::query()->findOrNew($id);

        return JsonResource::make($model)
            ->layout($layout->default($model));
    }

    public function store(PermissionGroupRequest $request, PermissionGroupLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->default());
    }

    public function update(PermissionGroupRequest $request, int $id, PermissionGroupLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->default());
    }

    public function destroy(PermissionGroupRequest $request, int $id, PermissionGroupLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->default());
    }

    public function select(PermissionGroupRequest $request)
    {
        return JsonResource::collection(
            PermissionsGroups::all()
                ->map(fn(PermissionsGroups $item) => [
                    'key'      => $item->getKey(),
                    'value'    => __('global.' . $item->lang_key),
                    'selected' => $request->input('selected') == $item->getKey(),
                ])
        );
    }
}
