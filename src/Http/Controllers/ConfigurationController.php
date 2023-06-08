<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Team64j\LaravelEvolution\Models\SystemSetting;
use Team64j\LaravelManagerApi\Http\Requests\ConfigurationRequest;
use Team64j\LaravelManagerApi\Http\Resources\ConfigurationResource;
use Team64j\LaravelManagerApi\Layouts\ConfigurationLayout;

class ConfigurationController extends Controller
{
    /**
     * @param ConfigurationRequest $request
     * @param ConfigurationLayout $layout
     *
     * @return ConfigurationResource
     */
    public function index(ConfigurationRequest $request, ConfigurationLayout $layout): ConfigurationResource
    {
        return (new ConfigurationResource(SystemSetting::all()->pluck('setting_value', 'setting_name')))
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'tab' => $layout->titleDefault(),
                ],
            ]);
    }

    /**
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

        return new ConfigurationResource([
            'reload' => true,
        ]);
    }
}
