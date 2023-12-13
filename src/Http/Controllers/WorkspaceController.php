<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\SystemSetting;
use Team64j\LaravelManagerApi\Http\Requests\WorkspaceRequest;
use Team64j\LaravelManagerApi\Http\Resources\WorkspaceResource;
use Team64j\LaravelManagerApi\Layouts\WorkspaceLayout;

class WorkspaceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/workspace",
     *     summary="Интерфейс и представление",
     *     tags={"System"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param WorkspaceRequest $request
     * @param WorkspaceLayout $layout
     *
     * @return WorkspaceResource
     */
    public function index(WorkspaceRequest $request, WorkspaceLayout $layout): WorkspaceResource
    {
        $data = [];

        $result = SystemSetting::query()
            ->where('setting_name', 'like', 'workspace%')
            ->get()
            ->pluck('setting_value', 'setting_name');

        foreach ($result as $key => $value) {
            [, $tab, $key] = explode('_', $key, 3);

            if (in_array($tab, ['topmenu', 'tree'])) {
                if (!$value || $value == '[]') {
                    continue;
                }

                $value = json_decode($value);
            }

            $data[$tab][$key] = $value;
        }

        if (empty($data['tree']['data'])) {
            $data['tree']['data'] = (new BootstrapController())->getSidebar(true);
        }

        if (empty($data['topmenu']['data'])) {
            $data['topmenu']['data'] = (new BootstrapController())->getMenu(true);
        }

        return WorkspaceResource::make($data)
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'title' => Lang::get('global.settings_ui'),
                    'icon' => $layout->getIcon(),
                    'lang' => [
                        'save' => Lang::get('global.save'),
                        'stay_new' => Lang::get('global.stay_new'),
                        'settings' => Lang::get('global.resource_setting'),
                        'select' => Lang::get('global.element_selector_title'),
                    ],
                ],
            ]);
    }

    /**
     * @OA\Post(
     *     path="/workspace",
     *     summary="Сохранение Интерфейс и представление",
     *     tags={"System"},
     *     security={{"Api":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param WorkspaceRequest $request
     * @param WorkspaceLayout $layout
     *
     * @return WorkspaceResource
     */
    public function store(WorkspaceRequest $request, WorkspaceLayout $layout): WorkspaceResource
    {
        $data = [];
        /** @var Collection $collect */
        $collect = Collection::make(
            Arr::dot(
                $request->only([
                    'dashboard',
                ]),
                'workspace_'
            )
        )
            ->keyBy(
                fn($value, $key) => str_replace('.', '_', $key)
            );

        if ($request->has('topmenu')) {
            $collect['workspace_topmenu_data'] = json_encode($request->get('topmenu')['data']);
        }

        if ($request->has('tree')) {
            $collect['workspace_tree_data'] = json_encode($request->get('tree')['data']);
        }

        foreach ($collect as $key => $value) {
            $data[] = [
                'setting_name' => $key,
                'setting_value' => $value,
            ];
        }

        SystemSetting::query()->upsert($data, 'setting_name');

        Cache::clear();

        Artisan::call('optimize:clear');
        Artisan::call('config:cache');

        return WorkspaceResource::make([])
            ->additional([
                'meta' => [
                    'reload' => true,
                ],
            ]);
    }
}
