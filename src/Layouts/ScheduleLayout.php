<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Illuminate\Support\Facades\Lang;
use Team64j\LaravelManagerApi\Components\Title;

class ScheduleLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            Title::make()
                ->setTitle(Lang::get('global.site_schedule'))
                ->setIcon('far fa-calendar')
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'far fa-calendar';
    }
}
