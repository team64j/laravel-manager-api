<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
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
    public function title(string $value = null): string
    {
        return $value ?? Lang::get('global.site_schedule');
    }

    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Title::make()
                ->setTitle($this->title())
                ->setIcon($this->icon())
        ];
    }
}
