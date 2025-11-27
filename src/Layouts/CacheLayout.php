<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Title;

class CacheLayout extends Layout
{
    public function title(): string
    {
        return __('global.refresh_site');
    }

    public function icon(): string
    {
        return 'fa fa-recycle';
    }

    public function default(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Actions::make()
                ->setClear($this->title(), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),
        ];
    }
}
