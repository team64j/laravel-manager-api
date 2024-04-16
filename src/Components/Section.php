<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

use Illuminate\Support\Arr;

class Section extends Component
{
    /**
     * @param string|null $icon
     * @param string|null $label
     * @param string|null $class
     * @param string|array|Component|null $slot
     */
    public function __construct(
        string $icon = null,
        string $label = null,
        string $class = null,
        string|array|Component $slot = null
    ) {
        $attributes = [
            'component' => 'AppSection',
            'attrs' => [
                'icon' => $icon,
                'label' => $label,
                'class' => $class,
            ],
            'slots' => [
                'default' => Arr::wrap($slot),
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIcon(string $value): static
    {
        $this->attributes['attrs']['icon'] = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLabel(string $value): static
    {
        $this->attributes['attrs']['label'] = $value;

        return $this;
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
     * @param string|array|Component|null $slot
     *
     * @return $this
     */
    public function putSlot(string|array|Component $slot = null): static
    {
        $this->attributes['slots']['default'][] = Arr::wrap($slot);

        return $this;
    }

    /**
     * @param string|array|Component|null $slot
     *
     * @return $this
     */
    public function setSlot(string|array|Component $slot = null): static
    {
        $this->attributes['slots']['default'] = (array) $slot;

        return $this;
    }
}
