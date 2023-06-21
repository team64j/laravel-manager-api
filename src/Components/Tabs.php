<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

use Illuminate\Support\Facades\Gate;

class Tabs extends Component
{
    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $attributes = [
            'component' => 'EvoTabs',
            'attrs' => [
                'id' => $data['id'] ?? null,
                'history' => $data['history'] ?? null,
                'data' => $data['data'] ?? [],
            ],
            'data' => $data['model'] ?? null,
            'slots' => $data['slots'] ?? null,
        ];

        parent::__construct($attributes);
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setId(string $value = null): static
    {
        $this->attributes['attrs']['id'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setUid(string $value = null): static
    {
        $this->attributes['attrs']['uid'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setClass(string $value = null): static
    {
        $this->attributes['attrs']['class'] = $value;

        return $this;
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

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setHistory(string $value = null): static
    {
        $this->attributes['attrs']['history'] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function isWatch(): static
    {
        $this->attributes['attrs']['watch'] = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function isLoadOnce(): static
    {
        $this->attributes['attrs']['loadOnce'] = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function isVertical(): static
    {
        $this->attributes['attrs']['vertical'] = true;

        return $this;
    }

    /**
     * @param string $id
     * @param string|null $name
     * @param string|null $icon
     * @param string|null $class
     * @param bool|array|string $permissions
     * @param string|array|null $route
     * @param string|null $title
     * @param array|Component|null $slot
     *
     * @return $this
     */
    public function addTab(
        string $id,
        string $name = null,
        string $icon = null,
        string $class = null,
        bool|array|string $permissions = true,
        string|array $route = null,
        string $title = null,
        array|Component $slot = null): static
    {
        if ($this->hasPermissions($permissions) &&
            !in_array($id, array_column($this->attributes['attrs']['data'], 'id'))
        ) {
            $data = get_defined_vars();

            if ($slot) {
                $this->attributes['slots'][$id] = $data['slot'];
                unset($data['slot']);
            }

            $this->attributes['attrs']['data'][] = $data;
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setTabs(array $data): static
    {
        $this->attributes['attrs']['data'] = $data;

        return $this;
    }

    /**
     * @param string $id
     * @param array|Component $data
     * @param bool|array|string $permissions
     *
     * @return $this
     */
    public function addSlot(string $id, array|Component $data = [], bool|array|string $permissions = true): static
    {
        if ($this->hasPermissions($permissions) && !isset($this->attributes['slots'][$id])) {
            $this->attributes['slots'][$id] = $data;
        }

        return $this;
    }

    /**
     * @param string $id
     * @param array|Component $data
     * @param bool|array|string $permissions
     *
     * @return $this
     */
    public function putSlot(string $id, array|Component $data = [], bool|array|string $permissions = true): static
    {
        if ($this->hasPermissions($permissions)) {
            $this->attributes['slots'][$id][] = $data;
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setSlots(array $data): static
    {
        $this->attributes['slots'] = $data;

        return $this;
    }

    /**
     * @param bool|array|string $permissions
     *
     * @return bool
     */
    protected function hasPermissions(bool|array|string $permissions = true): bool
    {
        if (is_bool($permissions)) {
            return $permissions;
        }

        return Gate::check($permissions);
    }
}
