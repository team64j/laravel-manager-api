<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResourceCollection extends AnonymousResourceCollection
{
    /**
     * @param $data
     *
     * @return $this
     */
    public function meta($data): static
    {
        $this->additional(array_merge($this->additional, ['meta' => $data]));

        return $this;
    }

    /**
     * @param $data
     *
     * @return $this
     */
    public function layout($data): static
    {
        $this->additional(array_merge($this->additional, ['layout' => $data]));

        return $this;
    }
}
