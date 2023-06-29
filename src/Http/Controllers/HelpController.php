<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use OpenApi\Annotations as OA;
use Team64j\LaravelManagerApi\Http\Requests\HelpRequest;
use Team64j\LaravelManagerApi\Http\Resources\HelpResource;

class HelpController extends Controller
{
    /**
     * @OA\Get(
     *     path="/help",
     *     summary="Получение раздела помощи",
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
     * @param HelpRequest $request
     *
     * @return HelpResource
     */
    public function index(HelpRequest $request): HelpResource
    {
        return HelpResource::make([]);
    }
}
