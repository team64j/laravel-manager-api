<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\ConfigurationRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Layouts\ConfigurationLayout;
use Team64j\LaravelManagerApi\Models\SystemSetting;

class ConfigurationController extends Controller
{
    #[OA\Get(
        path: '/configuration',
        summary: 'Чтение конфигурации',
        security: [['Api' => []]],
        tags: ['Configuration'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            )
        ]
    )]
    public function index(ConfigurationRequest $request, ConfigurationLayout $layout): JsonResource
    {
        $basePath = str_replace(DIRECTORY_SEPARATOR, '/', app()->basePath()) . '/';

        return JsonResource::make([
            'data' => SystemSetting::all()
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
                }),
        ])
            ->layout($layout->default());
    }

    #[OA\Post(
        path: '/configuration',
        summary: 'Сохранение конфигурации',
        security: [['Api' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(type: 'object')
        ),
        tags: ['Configuration'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            )
        ]
    )]
    public function store(ConfigurationRequest $request, SystemSetting $configuration): JsonResource
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

        return JsonResource::make([])
            ->meta([
                'reload' => true,
            ]);
    }
}
