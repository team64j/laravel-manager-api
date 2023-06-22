<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Team64j\LaravelManagerApi\Events\OnManagerMainFrameHeaderHTMLBlock;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array[]
     */
    protected $listen = [
        'OnManagerMainFrameHeaderHTMLBlock' => [
            OnManagerMainFrameHeaderHTMLBlock::class,
        ],
    ];
}
