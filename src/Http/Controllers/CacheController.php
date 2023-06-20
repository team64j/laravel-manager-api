<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;

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
    public function index()
    {

    }
}
