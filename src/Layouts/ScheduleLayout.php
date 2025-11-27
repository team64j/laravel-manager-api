<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Title;

class ScheduleLayout extends Layout
{
    /**
     * @return string
     */
    public function icon(): string
    {
        return 'far fa-calendar';
    }

    /**
     * @param string|null $value
     *
     * @return string
     */
    public function title(?string $value = null): string
    {
        return $value ?? __('global.site_schedule');
    }

    /**
     * @return array
     */
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
