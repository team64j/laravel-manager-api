<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use EvolutionCMS\Models\SystemSetting;
use Illuminate\Support\Facades\Artisan;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\ConfigurationRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
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
     * @return ApiResource
     */
    public function index(ConfigurationRequest $request, ConfigurationLayout $layout): ApiResource
    {
        $basePath = str_replace(DIRECTORY_SEPARATOR, '/', app()->basePath()) . '/';

        return ApiResource::make(
            SystemSetting::all()
                ->pluck('setting_value', 'setting_name')
                ->map(function ($value, $key) use ($basePath) {
                    if ($key == 'filemanager_path') {
                        $path = str_replace(DIRECTORY_SEPARATOR, '/', $value);

                        if ($path == $basePath) {
                            $value = '[(base_path)]';
                        }
                    }

                    if ($key == 'rb_base_dir') {
                        $value = str_replace($basePath, '[(base_path)]', $value);
                    }

                    if ($key == 'smtppw') {
                        $value = '**********';
                    }

                    return $value;
                })
        )
            ->layout($layout->default())
            ->meta([
                'title' => $layout->title(),
                'icon' => $layout->icon(),
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
     * @return ApiResource
     */
    public function store(ConfigurationRequest $request, SystemSetting $configuration): ApiResource
    {
        $data = [];
        $basePath = str_replace(DIRECTORY_SEPARATOR, '/', app()->basePath()) . '/';

        foreach ($request->all() as $key => $value) {
            if ($key == 'filemanager_path') {
                if ($value == '[(base_path)]') {
                    $value = $basePath;
                }
            }

            if ($key == 'rb_base_dir') {
                $value = str_replace('[(base_path)]', $basePath, (string) $value);
            }

            $data[] = [
                'setting_name' => $key,
                'setting_value' => $value,
            ];
        }

        $configuration->upsert($data, 'setting_name');

        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        return ApiResource::make([])
            ->meta([
                'reload' => true,
            ]);
    }
}
