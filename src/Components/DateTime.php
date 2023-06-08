<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class DateTime extends Input
{
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->attributes['attrs']['type'] = 'datetime-local';
    }
}
