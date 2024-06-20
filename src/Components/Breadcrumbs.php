<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Breadcrumbs extends Component
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes = [
            'component' => 'AppBreadcrumbs',
            'attrs' => [
                'data' => $attributes['data'] ?? [],
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param array|null $value
     *
     * @return $this
     */
    public function setData(array $value = null): static
    {
        $this->attributes['attrs']['data'] = $value;

        return $this;
    }
}
