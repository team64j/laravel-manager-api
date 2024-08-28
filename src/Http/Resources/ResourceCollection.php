<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Team64j\LaravelManagerApi\Traits\ResourceTrait;

class ResourceCollection extends AnonymousResourceCollection
{
    use ResourceTrait;
}
