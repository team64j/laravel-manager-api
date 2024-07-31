<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerComponents\Actions;
use Team64j\LaravelManagerComponents\Title;

class CacheLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Actions::make()
                ->setClear($this->title(), '', 'btn-red', 'fa fa-trash'),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return Lang::get('global.refresh_site');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'fa fa-recycle';
    }
}
