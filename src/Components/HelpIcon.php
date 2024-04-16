<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class HelpIcon extends Component
{
    /**
     * @param string|null $data
     * @param string|null $class
     */
    public function __construct(
        string $data = null,
        string $class = null
    ) {
        $attributes = [
            'component' => 'AppHelpIcon',
            'attrs' => [
                'icon' => $class,
                'data' => $data,
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInnerIcon(string $value): static
    {
        $this->attributes['attrs']['iconInner'] = $value;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function isOpacity(bool $value = true): static
    {
        $this->attributes['attrs']['noOpacity'] = !$value;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function isFit(bool $value = true): static
    {
        $this->attributes['attrs']['fit'] = $value;

        return $this;
    }
}
