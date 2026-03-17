<?php

namespace Team64j\LaravelManagerApi\Http\Controllers;

use App\Http\Controllers\Controller;
use Team64j\LaravelManagerApi\Http\Requests\PermissionAccessRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\PermissionAccessLayout;
use Team64j\LaravelManagerApi\Models\MembergroupName;

class PermissionAccessController extends Controller
{
    public function usersShow(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        $result = MembergroupName::all();

        return JsonResource::collection($result)
            ->layout($layout->users());
    }

    public function usersUpdate(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->users());
    }

    public function resourcesShow(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->resources());
    }

    public function resourcesUpdate(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->resources());
    }

    public function relationsShow(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->relations());
    }

    public function relationsUpdate(PermissionAccessRequest $request, PermissionAccessLayout $layout)
    {
        return JsonResource::make([])
            ->layout($layout->relations());
    }
}
