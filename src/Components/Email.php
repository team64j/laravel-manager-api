<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Email extends Input
{
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->attributes['attrs']['type'] = 'email';
    }
}
