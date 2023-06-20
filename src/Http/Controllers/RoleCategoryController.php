<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Team64j\LaravelEvolution\Models\PermissionsGroups;
use Team64j\LaravelManagerApi\Http\Requests\RoleCategoryRequest;
use Team64j\LaravelManagerApi\Http\Resources\RoleCategoryResource;
use Team64j\LaravelManagerApi\Layouts\RoleCategoryLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class RoleCategoryController extends Controller
{
    use PaginationTrait;

    /**
     * @param RoleCategoryRequest $request
     * @param RoleCategoryLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(RoleCategoryRequest $request, RoleCategoryLayout $layout): AnonymousResourceCollection
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

        return RoleCategoryResource::collection([
            'data' => [
                'data' => $data,
                'pagination' => $this->pagination($result),
            ],
            'layout' => $layout->list(),
            'meta' => [
                'tab' => $layout->titleList(),
            ],
        ]);
    }

    /**
     * @param RoleCategoryRequest $request
     * @param string $roleCategory
     * @param RoleCategoryLayout $layout
     *
     * @return RoleCategoryResource
     */
    public function show(
        RoleCategoryRequest $request,
        string $roleCategory,
        RoleCategoryLayout $layout): RoleCategoryResource
    {
        /** @var PermissionsGroups $roleCategory */
        $roleCategory = PermissionsGroups::query()->findOrNew($roleCategory);

        if (!$roleCategory->getKey()) {
            $roleCategory->setRawAttributes([
                'name' => ''
            ]);
        }

        return RoleCategoryResource::make([
            'data' => [],
            'layout' => $layout->default($roleCategory),
            'meta' => [
                'tab' => $layout->titleDefault($roleCategory),
            ],
        ]);
    }
}
