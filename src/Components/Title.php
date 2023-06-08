<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Title extends Component
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $attributes = [
            'component' => 'Title',
            'attrs' => [
                'title' => $data['title'] ?? null,
                'icon' => $data['icon'] ?? null,
                'id' => $data['id'] ?? null,
                'help' => $data['help'] ?? null,
            ],
            'model' => $data['model'] ?? null,
            'data' => $data['data'] ?? null,
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setTitle(string $value = null): static
    {
        $this->attributes['attrs']['title'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setIcon(string $value = null): static
    {
        $this->attributes['attrs']['icon'] = $value;

        return $this;
    }

    /**
     * @param string|int|null $value
     *
     * @return $this
     */
    public function setId(string | int $value = null): static
    {
        $this->attributes['attrs']['id'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setModel(string $value = null): static
    {
        $this->attributes['model'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setData(string $value = null): static
    {
        $this->attributes['data'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setHelp(string $value = null): static
    {
        $this->attributes['attrs']['help'] = $value;

        return $this;
    }
}
