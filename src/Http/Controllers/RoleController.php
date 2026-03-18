<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Team64j\LaravelManagerApi\Http\Requests\RoleRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\RoleLayout;
use Team64j\LaravelManagerApi\Models\UserRole;
use Throwable;

class RoleController extends Controller
{
    public function index(RoleRequest $request, RoleLayout $layout)
    {
        $result = UserRole::query()->paginate(config('global.number_of_results'));

        return JsonResource::collection(
            $result->setCollection(
                $result
                    ->getCollection()
                    ->map(fn(UserRole $role) => $role->setAttribute('@disabled', $role->getKey() == 1))
            )
        )
            ->layout($layout->list());
    }

    protected function store(RoleRequest $request, RoleLayout $layout)
    {
        $model = UserRole::query()->create($request->validated());

        return JsonResource::make($model)
            ->layout($layout->default($model));
    }

    /**
     * @throws Throwable
     */
    public function show(RoleRequest $request, int $id, RoleLayout $layout)
    {
        throw_if($id == 1, message: __('global.administrator_role_message'));

        $model = UserRole::query()->findOrNew($id);

        $model->setRelation('permissions', $model->permissions->pluck('id'));

        return JsonResource::make($model)
            ->layout($layout->default($model));
    }

    /**
     * @throws Throwable
     */
    protected function update(RoleRequest $request, int $id, RoleLayout $layout)
    {
        throw_if($id == 1, message: __('global.administrator_role_message'));

        $model = UserRole::query()->findOrFail($id);
        $model->update($request->validated());

        return JsonResource::make($model)
            ->layout($layout->default($model));
    }

    /**
     * @throws Throwable
     */
    protected function destroy(RoleRequest $request, int $id, RoleLayout $layout)
    {
        throw_if($id == 1, message: __('global.administrator_role_message'));
    }

    public function list(RoleRequest $request)
    {
        return JsonResource::collection(
            UserRole::query()->paginate(config('global.number_of_results'))
        )
            ->meta([
                'route'   => api_url('roles.show', [':id']),
                'prepend' => [
                    [
                        'name' => __('global.new_role'),
                        'icon' => 'fa fa-plus-circle',
                        'to'   => [
                            'path' => api_url('roles.show', [0]),
                        ],
                    ],
                ],
            ]);
    }
}
