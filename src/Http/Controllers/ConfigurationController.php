<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;
use Team64j\LaravelEvolution\Models\SystemSetting;
use Team64j\LaravelManagerApi\Http\Requests\ConfigurationRequest;
use Team64j\LaravelManagerApi\Http\Resources\ConfigurationResource;
use Team64j\LaravelManagerApi\Layouts\ConfigurationLayout;

class ConfigurationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/configuration",
     *     summary="Чтение конфигурации",
     *     tags={"Configuration"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param ConfigurationRequest $request
     * @param ConfigurationLayout $layout
     *
     * @return ConfigurationResource
     */
    public function index(ConfigurationRequest $request, ConfigurationLayout $layout): ConfigurationResource
    {
        return ConfigurationResource::make(
            SystemSetting::all()
                ->pluck('setting_value', 'setting_name')
        )
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'tab' => $layout->titleDefault(),
                ],
            ]);
    }

    /**
     * @OA\Post(
     *     path="/configuration",
     *     summary="Сохранение конфигурации",
     *     tags={"Configuration"},
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
     * @param ConfigurationRequest $request
     * @param SystemSetting $configuration
     *
     * @return ConfigurationResource
     */
    public function store(ConfigurationRequest $request, SystemSetting $configuration): ConfigurationResource
    {
        $data = [];

        foreach ($request->all() as $key => $value) {
            $data[] = [
                'setting_name' => $key,
                'setting_value' => $value,
            ];
        }

        $configuration->upsert($data, 'setting_name');

        Cache::clear();

        Artisan::call('optimize:clear');
        Artisan::call('config:cache');

        return ConfigurationResource::make([])
            ->additional([
                'meta' => [
                    'reload' => true,
                ],
            ]);
    }
}
