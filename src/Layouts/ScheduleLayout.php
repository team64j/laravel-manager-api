<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Title;

class ScheduleLayout extends Layout
{
    public function icon(): string
    {
        return 'far fa-calendar';
    }

    public function title(?string $value = null): string
    {
        return $value ?? __('global.site_schedule');
    }

    public function default(): array
    {
        return [
            GlobalTab::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),

            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon()),
        ];
    }
}
