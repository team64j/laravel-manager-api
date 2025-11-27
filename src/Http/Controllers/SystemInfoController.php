<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Team64j\LaravelManagerApi\Http\Requests\SystemInfoRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Layouts\SystemInfoLayout;

class SystemInfoController extends Controller
{
    public function __construct(protected SystemInfoLayout $layout) {}

    #[OA\Get(
        path: '/system-info',
        summary: 'Системная информация',
        security: [['Api' => []]],
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function index(SystemInfoRequest $request): JsonResourceCollection
    {
        $data = [
            [
                'name'  => __('global.modx_version'),
                'value' => config('global.settings_version'),
            ],
            [
                'name'  => __('global.release_date'),
                'value' => '',//$this->managerTheme->getCore()->getVersionData('release_date')
            ],
            [
                'name'       => 'PHP Version',
                'value.html' => '<a href="/phpinfo" style="text-decoration: underline">' . phpversion() . '</a>',
            ],
            [
                'name'  => __('global.access_permissions'),
                'value' => __('global.' . (config('global.use_udperms') ? 'enabled' : 'disabled')),
            ],
            [
                'name'  => __('global.servertime'),
                'value' => Carbon::now()->toTimeString(),
            ],
            [
                'name'  => __('global.localtime'),
                'value' => Carbon::now()->addHours((int) config('global.server_offset_time'))->toTimeString(),
            ],
            [
                'name'  => __('global.serveroffset'),
                'value' => config('global.server_offset_time') . ' h',
            ],
            [
                'name'  => __('global.database_name'),
                'value' => DB::connection()->getDatabaseName(),
            ],
            [
                'name'  => __('global.database_server'),
                'value' => DB::connection()->getConfig('host'),
            ],
            [
                'name'  => __('global.database_version'),
                'value' => DB::connection()->getPdo()->getAttribute(DB::connection()->getPdo()::ATTR_CLIENT_VERSION) .
                    ' - ' . DB::connection()->getPdo()->getAttribute(DB::connection()->getPdo()::ATTR_SERVER_VERSION),
            ],
            [
                'name'  => __('global.database_charset'),
                'value' => $this->resolveCharset(),
            ],
            [
                'name'  => __('global.database_collation'),
                'value' => $this->resolveCollation(),
            ],
            [
                'name'  => __('global.table_prefix'),
                'value' => DB::connection()->getTablePrefix(),
            ],
        ];

        $constants = get_defined_constants();
        ksort($constants);

        foreach ($constants as $key => $value) {
            if (str($key)->startsWith(['MODX_', 'EVO_'])) {
                $data[] = [
                    'name'  => $key,
                    'value' => $value,
                ];
            }
        }

        return JsonResource::collection($data)
            ->layout($this->layout->default());
    }

    #[OA\Get(
        path: '/phpinfo',
        summary: 'Информация о PHP',
        security: [['Api' => []]],
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ok',
                content: new OA\JsonContent(type: 'object')
            ),
        ]
    )]
    public function phpinfo(SystemInfoRequest $request): string
    {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_get_clean();

        $path = url('/') . str_replace([app()->basePath(), DIRECTORY_SEPARATOR], ['', '/'], dirname(__DIR__, 3));

        $style = '
        <script src="' . $path . '/resources/js/message.js?v=1.1.1"></script>
        <link rel="stylesheet" href="' . $path . '/resources/css/phpinfo-reset.css?v=1.1.1">
        <link rel="stylesheet" href="' . $path . '/resources/css/phpinfo-main.css?v=1.1.1">
        ';

        return (string) preg_replace('/<head>(.*?)<\/head>/s', '$1' . $style, $info);
    }

    protected function resolveCharset()
    {
        return match (config('database.default')) {
            'pgsql' => '',
            'mysql' => DB::selectOne("show variables like 'character_set_database'")->Value,
            default => 'none',
        };
    }

    protected function resolveCollation()
    {
        return match (config('database.default')) {
            'pgsql' => '',
            'mysql' => DB::selectOne("show variables like 'collation_database'")->Value,
            default => 'none',
        };
    }
}
