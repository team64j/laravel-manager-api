<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Traits;

trait JsonResourceTrait
{
    public function meta($data): static
    {
        $this->additional['meta'] = $data;

        return $this;
    }

    public function layout($data): static
    {
        $this->additional['layout'] = $data;

        return $this;
    }
}
