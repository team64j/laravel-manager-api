<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use SimpleXMLElement;
use Team64j\LaravelManagerApi\Http\Requests\DashboardRequest;
use Team64j\LaravelManagerApi\Http\Resources\JsonResourceCollection;
use Team64j\LaravelManagerApi\Http\Resources\JsonResource;
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
     * @return JsonResourceCollection
     */
    public function index(DashboardRequest $request, DashboardLayout $layout): JsonResourceCollection
    {
        return JsonResource::collection([
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
        $data = cache()->remember('cms.dashboard.news', 86400, function () {
            $data = [];

            if (config('global.rss_url_news')) {
                /** @var SimpleXMLElement $result */
                $result = simplexml_load_string(
                    Http::get(config('global.rss_url_news'))->body()
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
            'meta' => !$data ? ['message' => __('global.not_set')] : [],
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
        $data = cache()->remember('cms.dashboard.news-security', 86400, function () {
            $data = [];

            if (config('global.rss_url_security')) {
                /** @var SimpleXMLElement $result */
                $result = simplexml_load_string(
                    Http::get(config('global.rss_url_security'))->body()
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
            'meta' => !$data ? ['message' => __('global.not_set')] : [],
        ];
    }
}
