<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use Team64j\LaravelManagerComponents\GlobalTab;
use Team64j\LaravelManagerComponents\Title;

class HelpLayout extends Layout
{
    /**
     * @return string
     */
    public function title(): string
    {
        return __('global.help');
    }

    /**
     * @return string
     */
    public function icon(): string
    {
        return 'far fa-question-circle';
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
