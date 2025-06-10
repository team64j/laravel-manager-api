<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Traits;

trait JsonResourceTrait
{
    /**
     * @param $data
     *
     * @return $this
     */
    public function meta($data): static
    {
        $this->additional['meta'] = $data;

        return $this;
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function layout($data): static
    {
        $this->additional['layout'] = $data;

        return $this;
    }
}
