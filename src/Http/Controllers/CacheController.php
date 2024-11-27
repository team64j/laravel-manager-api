<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Resources\ApiResource;
use Team64j\LaravelManagerApi\Layouts\CacheLayout;

class CacheController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cache",
     *     summary="Очистка кэша",
     *     tags={"Cache"},
     *     security={{"Api":{}}},
     *     @OA\Response(
     *          response="200",
     *          description="ok",
     *          @OA\JsonContent(
     *              type="object"
     *          )
     *      )
     * )
     *
     * @return void
     */
    public function index(CacheLayout $layout)
    {
        return ApiResource::make([])
            ->layout($layout->default())
            ->meta([
                'icon' => $layout->icon(),
                'title' => $layout->title(),
            ]);
    }
}
