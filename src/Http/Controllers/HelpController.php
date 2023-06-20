<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Controllers;

use Team64j\LaravelManagerApi\Http\Requests\HelpRequest;
use Team64j\LaravelManagerApi\Http\Resources\HelpResource;

class HelpController extends Controller
{
    /**
     * @param HelpRequest $request
     *
     * @return HelpResource
     */
    public function index(HelpRequest $request): HelpResource
    {
        return HelpResource::make([]);
    }
}
