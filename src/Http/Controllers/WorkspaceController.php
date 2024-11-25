<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\SystemSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\WorkspaceRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\WorkspaceLayout;

class WorkspaceController extends Controller
{
    public function __construct(protected WorkspaceLayout $layout)
    {
    }

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
     *
     * @return JsonResource
     */
    public function index(WorkspaceRequest $request): JsonResource
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

        return JsonResource::make($data)
            ->layout($this->layout->default())
            ->meta([
                'title' => $this->layout->title(),
                'icon' => $this->layout->icon(),
                'lang' => [
                    'save' => Lang::get('global.save'),
                    'stay_new' => Lang::get('global.stay_new'),
                    'settings' => Lang::get('global.resource_setting'),
                    'select' => Lang::get('global.element_selector_title'),
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
     *
     * @return JsonResource
     */
    public function store(WorkspaceRequest $request): JsonResource
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

        return JsonResource::make([])
            ->meta([
                'reload' => true,
            ]);
    }
}
