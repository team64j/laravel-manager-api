<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\SystemInfoRequest;
use Team64j\LaravelManagerApi\Http\Resources\SystemInfoResource;
use Team64j\LaravelManagerApi\Layouts\SystemInfoLayout;

class SystemInfoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/system-info",
     *     summary="Системная информация",
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
     * @param SystemInfoRequest $request
     * @param SystemInfoLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(SystemInfoRequest $request, SystemInfoLayout $layout): AnonymousResourceCollection
    {
        $data = [
            [
                'name' => Lang::get('global.modx_version'),
                'value' => Config::get('global.settings_version'),
            ],
            [
                'name' => Lang::get('global.release_date'),
                'value' => '',//$this->managerTheme->getCore()->getVersionData('release_date')
            ],
            [
                'name' => 'PHP Version',
                'value.html' => '<a href="/phpinfo" style="text-decoration: underline">' . phpversion() . '</a>',
            ],
            [
                'name' => Lang::get('global.access_permissions'),
                'value' => Lang::get('global.' . (Config::get('global.use_udperms') ? 'enabled' : 'disabled')),
            ],
            [
                'name' => Lang::get('global.servertime'),
                'value' => Carbon::now()->toTimeString(),
            ],
            [
                'name' => Lang::get('global.localtime'),
                'value' => Carbon::now()->addHours(Config::get('global.server_offset_time'))->toTimeString(),
            ],
            [
                'name' => Lang::get('global.serveroffset'),
                'value' => Config::get('global.server_offset_time') . ' h',
            ],
            [
                'name' => Lang::get('global.database_name'),
                'value' => DB::connection()->getDatabaseName(),
            ],
            [
                'name' => Lang::get('global.database_server'),
                'value' => DB::connection()->getConfig('host'),
            ],
            [
                'name' => Lang::get('global.database_version'),
                'value' => DB::connection()->getPdo()->getAttribute(DB::connection()->getPdo()::ATTR_SERVER_VERSION),
            ],
            [
                'name' => Lang::get('global.database_charset'),
                'value' => $this->resolveCharset(),
            ],
            [
                'name' => Lang::get('global.database_collation'),
                'value' => $this->resolveCollation(),
            ],
            [
                'name' => Lang::get('global.table_prefix'),
                'value' => DB::connection()->getTablePrefix(),
            ],
        ];

        foreach (get_defined_constants() as $key => $value) {
            if (Str::startsWith($key, ['MODX_', 'EVO_'])) {
                $data[] = [
                    'name' => $key,
                    'value' => $value,
                ];
            }
        }

        return SystemInfoResource::collection($data)
            ->additional([
                'layout' => $layout->default(),
                'meta' => [
                    'title' => Lang::get('global.view_sysinfo'),
                    'icon' => $layout->getIcon(),
                ],
            ]);
    }

    /**
     * @OA\Get(
     *     path="/phpinfo",
     *     summary="Информация о PHP",
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
     * @param SystemInfoRequest $request
     *
     * @return string
     */
    public function phpinfo(SystemInfoRequest $request): string
    {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_get_clean();

        $path = URL::to('/') . str_replace([App::basePath(), DIRECTORY_SEPARATOR], ['', '/'], dirname(__DIR__, 3));

        $style = '
        <link rel="stylesheet" href="' . $path . '/resources/css/phpinfo-reset.css?v=' . time() . '">
        <link rel="stylesheet" href="' . $path . '/resources/css/phpinfo-main.css?v=' . time() . '">
        ';

        return (string) preg_replace('/<head>(.*?)<\/head>/s', '$1' . $style, $info);
    }

    protected function resolveCharset()
    {
        switch (Config::get('database.default')) {
            case 'pgsql':
//                $result = $this->database->query("SELECT * FROM pg_settings WHERE name='client_encoding'");
//                $charset = $this->database->getRow($result, 'num');
//
//                return $charset[1];

                return '';

            case 'mysql':
                return DB::selectOne("show variables like 'character_set_database'")->Value;

            default :
                return 'none';
        }
    }

    protected function resolveCollation()
    {
        switch (Config::get('database.default')) {
            case 'pgsql':
//                $result = $this->database->query("SELECT * FROM pg_settings WHERE name = 'lc_collate'");
//                $charset = $this->database->getRow($result, 'num');
//
//                return $charset[1];

                return '';

            case 'mysql':
                return DB::selectOne("show variables like 'collation_database'")->Value;

            default :
                return 'none';
        }
    }
}
