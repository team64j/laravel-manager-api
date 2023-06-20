<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\DocumentgroupName;
use Team64j\LaravelEvolution\Models\MembergroupName;
use Team64j\LaravelEvolution\Models\SiteContent;
use Team64j\LaravelEvolution\Models\User;
use Team64j\LaravelManagerApi\Http\Requests\PermissionRequest;
use Team64j\LaravelManagerApi\Http\Resources\PermissionResource;
use Team64j\LaravelManagerApi\Layouts\PermissionGroupLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionRelationLayout;
use Team64j\LaravelManagerApi\Layouts\PermissionResourceLayout;
use Team64j\LaravelManagerApi\Models\Permissions;
use Team64j\LaravelManagerApi\Models\PermissionsGroups;

class PermissionController extends Controller
{
    /**
     * @param PermissionRequest $request
     * @param PermissionGroupLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function groups(PermissionRequest $request, PermissionGroupLayout $layout): AnonymousResourceCollection
    {
        $groups = MembergroupName::query()
            ->with('users')
            ->orderBy('name')
            ->get()
            ->map(function (MembergroupName $group) {
                if ($group->users->count()) {
                    $users = $group
                        ->users
                        ->map(fn(User $i) => '
                            <a href="users/' . $i->getKey() . '" class="mr-1 link">' . $i->username . '</a>'
                        )
                        ->join(' ');
                } else {
                    $users = '<span class="opacity-50">' . Lang::get('global.access_permissions_no_users_in_group') .
                        '</span>';
                }

                return $group->setAttribute('users.html', $users);
            });

        return PermissionResource::collection([
            'data' => [
                'data' => $groups,
            ],
            'layout' => $layout->list(),
            'meta' => [
                'tab' => $layout->titleList(),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionGroupLayout $layout
     *
     * @return PermissionResource
     */
    public function group(
        PermissionRequest $request,
        string $id,
        PermissionGroupLayout $layout): PermissionResource
    {
        $data = MembergroupName::query()->findOrNew($id);

        return PermissionResource::make([
            'data' => $data,
            'layout' => $layout->default($data),
            'meta' => [
                'tab' => $layout->titleDefault($data),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     * @param PermissionResourceLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function resources(
        PermissionRequest $request,
        PermissionResourceLayout $layout): AnonymousResourceCollection
    {
        $resources = DocumentgroupName::query()
            ->with('documents')
            ->orderBy('name')
            ->get()
            ->map(function (DocumentgroupName $group) {
                if ($group->documents->count()) {
                    $documents = $group
                        ->documents
                        ->map(fn(SiteContent $i) => '
                            <a href="document/' . $i->getKey() . '" class="mr-1 link">' . $i->pagetitle . ' (' .
                            $i->id . ')</a> '
                        )
                        ->join(' ');
                } else {
                    $documents =
                        '<span class="opacity-50">' . Lang::get('global.access_permissions_no_resources_in_group') .
                        '</span>';
                }

                return $group->setAttribute('documents.html', $documents);
            });

        return PermissionResource::collection([
            'data' => [
                'data' => $resources,
            ],
            'layout' => $layout->list(),
            'meta' => [
                'tab' => $layout->titleList(),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionResourceLayout $layout
     *
     * @return PermissionResource
     */
    public function resource(
        PermissionRequest $request,
        string $id,
        PermissionResourceLayout $layout): PermissionResource
    {
        $data = DocumentgroupName::query()->findOrNew($id);

        return PermissionResource::make([
            'data' => $data,
            'layout' => $layout->default($data),
            'meta' => [
                'tab' => $layout->titleDefault($data),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     * @param PermissionRelationLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function relations(
        PermissionRequest $request,
        PermissionRelationLayout $layout): AnonymousResourceCollection
    {
        $groups = MembergroupName::query()
            ->with('documentGroups')
            ->orderBy('name')
            ->get()
            ->map(function (MembergroupName $group) {
                if ($group->documentGroups->count()) {
                    $documentGroups = $group
                        ->documentGroups
                        ->map(fn(DocumentgroupName $i) => '
                            <div class="pb-1">
                                <span class="font-medium">' . $i->name . '</span> (' .
                            ($i->pivot->context ? 'web' : 'mgr') . ')
                                <a class="link text-rose-500 hover:text-rose-600"></a>
                            </div>'
                        )
                        ->join(' ');
                } else {
                    $documentGroups = '<span class="opacity-50">' . Lang::get('global.no_groups_found') . '</span>';
                }

                return $group->setAttribute('document_groups.html', $documentGroups);
            });

        $documents = DocumentgroupName::query()
            ->with('documents')
            ->orderBy('name')
            ->get();

        return PermissionResource::collection([
            'data' => [
                'data' => $groups,
            ],
            'layout' => $layout->list($groups, $documents),
            'meta' => [
                'tab' => $layout->titleList(),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     * @param string $id
     * @param PermissionRelationLayout $layout
     *
     * @return PermissionResource
     */
    public function relation(
        PermissionRequest $request,
        string $id,
        PermissionRelationLayout $layout): PermissionResource
    {
        $data = MembergroupName::query()->findOrNew($id);

        return PermissionResource::make([
            'data' => $data,
            'layout' => $layout->default($data),
            'meta' => [
                'tab' => $layout->titleDefault($data),
            ],
        ]);
    }

    /**
     * @param PermissionRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function select(PermissionRequest $request): AnonymousResourceCollection
    {
        $selected = $request->input('selected') ?: [];

        if ($selected && is_string($selected)) {
            $selected = explode(',', $selected);
        }

        return PermissionResource::collection(
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
