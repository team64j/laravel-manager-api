<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Template extends Component
{
    /**
     * @param string|null $class
     * @param string|array|null $slot
     */
    public function __construct(
        string $class = null,
        string | array $slot = null
    ) {
        $attributes = [
            'component' => 'EvoTemplate',
            'attrs' => [
                'class' => $class,
            ],
            'slots' => [
                'default' => [(array) $slot],
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setClass(string $value): static
    {
        $this->attributes['attrs']['class'] = $value;

        return $this;
    }

    /**
     * @param string|array|null $slot
     *
     * @return $this
     */
    public function putSlot(string | array $slot = null): static
    {
        $this->attributes['slots']['default'][] = (array) $slot;

        return $this;
    }

    /**
     * @param string|array|null $slot
     *
     * @return $this
     */
    public function setSlot(string | array $slot = null): static
    {
        $this->attributes['slots']['default'] = (array) $slot;

        return $this;
    }
}
