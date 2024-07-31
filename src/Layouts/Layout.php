<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

abstract class Layout
{
    /**
     * @return array
     */
    abstract public function default(): array;

    /**
     * @return array
     */
    public function list(): array
    {
        return [];
    }

    /**
     * @return string
     */
    abstract public function title(): string;

    /**
     * @return string
     */
    public function titleList(): string
    {
        return '';
    }

    /**
     * @return string
     */
    abstract public function icon(): string;

    /**
     * @return string
     */
    public function iconList(): string
    {
        return '';
    }
}
