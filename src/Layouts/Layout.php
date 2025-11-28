<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

abstract class Layout
{
    abstract public function default(): array;

    public function list(): array
    {
        return [];
    }

    abstract public function title(): string;

    public function titleList(): string
    {
        return '';
    }

    abstract public function icon(): string;

    public function iconList(): string
    {
        return '';
    }
}
