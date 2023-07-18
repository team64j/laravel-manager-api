<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\DashboardRequest;
use Team64j\LaravelManagerApi\Http\Resources\DashboardResource;
use Team64j\LaravelManagerApi\Layouts\DashboardLayout;
use Team64j\LaravelManagerApi\Traits\PaginationTrait;

class DashboardController extends Controller
{
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/dashboard",
     *     summary="Получение шаблона для стартовой панели",
     *     tags={"Dashboard"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DashboardRequest $request
     * @param DashboardLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function index(DashboardRequest $request, DashboardLayout $layout): AnonymousResourceCollection
    {
        return DashboardResource::collection([
            //'widgetDocuments' => $this->getDocuments(),
        ])
            ->additional([
                'meta' => [],
                'layout' => $layout->default(),
            ]);
    }

//    /**
//     * @param DashboardRequest $request
//     *
//     * @return Application|Factory|View
//     */
//    public function show(DashboardRequest $request): View | Factory | Application
//    {
//        $userAttributes = Auth::user()->attributes;
//
//        return view('dashboard', [
//            'user' => [
//                'username' => Auth::user()->username,
//                'role' => $userAttributes->role,
//                'permissions' => $userAttributes->rolePermissions->pluck('permission'),
//            ],
//            'config' => [
//                'site_url' => URL::to('/', [], Config::get('global.server_protocol') == 'https'),
//            ],
//            'lexicon' => Lang::get('global'),
//        ]);
//    }

    /**
     * @OA\Get(
     *     path="/dashboard/news",
     *     summary="Получение списка новостей для дашборда",
     *     tags={"Dashboard"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DashboardRequest $request
     *
     * @return array[]
     */
    public function news(DashboardRequest $request): array
    {
        $data = Cache::remember('cms.dashboard.news', 86400, function () {
            $data = [];

            if (Config::get('global.rss_url_news')) {
                $result = \simplexml_load_string(
                    Http::get(Config::get('global.rss_url_news'))
                        ->body()
                );

                foreach ($result->entry as $item) {
                    $content = strip_tags((string) $item->content);

                    if (strlen($content) > 199) {
                        $content = Str::words($content, 15, '...');
                        $content .= '<br />Read <a href="' . $item->link['href'] . '" target="_blank">more</a>.';
                    }

                    $data[] = [
                        'content' => '<a href="' . $item->link['href'] . '" target="_blank">' . $item->title .
                            '</a> - <strong>' .
                            $item->updated . '</strong><div class="text-sm">' . $content . '</div>',
                    ];
                }
            }

            return $data;
        });

        return [
            'data' => [
                'data' => $data,
            ],
        ];
    }

    /**
     * @OA\Get(
     *     path="/dashboard/news-security",
     *     summary="Получение списка новостей по безопасности для дашборда",
     *     tags={"Dashboard"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DashboardRequest $request
     *
     * @return array[]
     */
    public function newsSecurity(DashboardRequest $request): array
    {
        $data = Cache::remember('cms.dashboard.news-security', 86400, function () {
            $data = [];

            if (Config::get('global.rss_url_security')) {
                $result = \simplexml_load_string(
                    Http::get(Config::get('global.rss_url_security'))
                        ->body()
                );

                foreach ($result->entry as $item) {
                    $content = strip_tags((string) $item->content);

                    if (strlen($content) > 199) {
                        $content = Str::words($content, 15, '...');
                        $content .= '<br />Read <a href="' . $item->link['href'] . '" target="_blank">more</a>.';
                    }

                    $data[] = [
                        'content' => '<a href="' . $item->link['href'] . '" target="_blank">' . $item->title .
                            '</a> - <strong>' .
                            $item->updated . '</strong><div class="text-sm">' . $content . '</div>',
                    ];
                }
            }

            return $data;
        });

        return [
            'data' => [
                'data' => $data,
            ],
        ];
    }

    /**
     * @OA\Get(
     *     path="/dashboard/sidebar",
     *     summary="Получение шаблона сайдбара",
     *     tags={"Dashboard"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     * @param DashboardRequest $request
     * @param DashboardLayout $layout
     *
     * @return AnonymousResourceCollection
     */
    public function sidebar(DashboardRequest $request, DashboardLayout $layout): AnonymousResourceCollection
    {
        return DashboardResource::collection([
            'data' => [],
            'meta' => [],
            'layout' => $layout->sidebar(),
        ]);
    }
}
