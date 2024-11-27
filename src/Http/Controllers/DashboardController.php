<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use SimpleXMLElement;
use Team64j\LaravelManagerApi\Http\Requests\DashboardRequest;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Http\Resources\ApiCollection;
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
     * @return ApiCollection
     */
    public function index(DashboardRequest $request, DashboardLayout $layout): ApiCollection
    {
        return ApiResource::collection([
            //'widgetDocuments' => $this->getDocuments(),
        ])
            ->layout($layout->default());
    }

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
                /** @var SimpleXMLElement $result */
                $result = simplexml_load_string(
                    Http::get(Config::get('global.rss_url_news'))->body()
                );

                if ($result instanceof SimpleXMLElement) {
                    foreach ($result->entry as $item) {
                        $content = strip_tags((string) $item->content);

                        if (strlen($content) > 199) {
                            $content = Str::words($content, 15, '...');
                            $content .= '<br />Read <a href="' . $item->link['href'] .
                                '" target="_blank">more</a>.';
                        }

                        $data[] = [
                            'content' => '<a href="' . $item->link['href'] . '" target="_blank">' . $item->title .
                                '</a> - <strong>' .
                                $item->updated . '</strong><div class="text-sm">' . $content . '</div>',
                        ];
                    }
                }
            }

            return $data;
        });

        return [
            'data' => $data,
            'meta' => !$data ? ['message' => Lang::get('global.not_set')] : [],
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
                /** @var SimpleXMLElement $result */
                $result = simplexml_load_string(
                    Http::get(Config::get('global.rss_url_security'))->body()
                );

                if ($result instanceof SimpleXMLElement) {
                    foreach ($result->entry as $item) {
                        $content = strip_tags((string) $item->content);

                        if (strlen($content) > 199) {
                            $content = Str::words($content, 15, '...');
                            $content .= '<br />Read <a href="' . $item->link['href'] .
                                '" target="_blank">more</a>.';
                        }

                        $data[] = [
                            'content' => '<a href="' . $item->link['href'] . '" target="_blank">' . $item->title .
                                '</a> - <strong>' .
                                $item->updated . '</strong><div class="text-sm">' . $content . '</div>',
                        ];
                    }
                }
            }

            return $data;
        });

        return [
            'data' => $data,
            'meta' => !$data ? ['message' => Lang::get('global.not_set')] : [],
        ];
    }
}
