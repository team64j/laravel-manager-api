<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Tree extends Component
{
    /**
     * @param array $data
     */
    public function __construct(array $data = []) {
        $attributes = [
            'component' => 'EvoTree',
            'attrs' => [...$data],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setId(string $value): static
    {
        $this->attributes['attrs']['id'] = $value;

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
     * @param string $value
     *
     * @return $this
     */
    public function setUrl(string $value): static
    {
        $this->attributes['attrs']['url'] = $value;

        return $this;
    }

    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function setRoute(string|array $value): static
    {
        $this->attributes['attrs']['route'] = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRouteList(string $value): static
    {
        $this->attributes['attrs']['routeList'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setAliases(array $value): static
    {
        $this->attributes['attrs']['aliases'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setIcons(array $value): static
    {
        $this->attributes['attrs']['icons'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setTemplates(array $value): static
    {
        $this->attributes['attrs']['templates'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setAppends(array $value): static
    {
        $this->attributes['attrs']['appends'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setContextMenu(array $value): static
    {
        $this->attributes['attrs']['contextMenu'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setMenu(array $value): static
    {
        $this->attributes['attrs']['menu'] = $value;

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setSettings(array $value): static
    {
        $this->attributes['attrs']['settings'] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function isCategory(): static
    {
        $this->attributes['attrs']['category'] = true;

        return $this;
    }
}
