<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Tab extends Component
{
    /**
     * @param string|null $id
     * @param string|null $name
     * @param string|null $icon
     * @param string|null $class
     * @param bool|array|string $permissions
     * @param string|array|null $route
     * @param string|null $title
     * @param array|Component|null $slot
     * @param bool $needUpdate
     */
    public function __construct(
        string $id = null,
        string $name = null,
        string $icon = null,
        string $class = null,
        bool|array|string $permissions = true,
        string|array $route = null,
        string $title = null,
        array|Component $slot = null,
        bool $needUpdate = false)
    {
        if ($this->hasPermissions($permissions)) {
            parent::__construct([
                'id' => $id,
                'name' => $name,
                'title' => $title,
                'icon' => $icon,
                'class' => $class,
                'route' => $route,
                'needUpdate' => $needUpdate,
                'slot' => $slot,
            ]);
        }
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setId(string $value = null): static
    {
        $this->attributes['id'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setName(string $value = null): static
    {
        $this->attributes['name'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setTitle(string $value = null): static
    {
        $this->attributes['title'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setIcon(string $value = null): static
    {
        $this->attributes['icon'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setClass(string $value = null): static
    {
        $this->attributes['class'] = $value;

        return $this;
    }

    /**
     * @param string|array|null $value
     *
     * @return $this
     */
    public function setRoute(string|array $value = null): static
    {
        $this->attributes['route'] = $value;

        return $this;
    }

    /**
     * @param array|Component $value
     *
     * @return $this
     */
    public function setSlot(array|Component $value): static
    {
        $this->attributes['slot'] = $value;

        return $this;
    }

    /**
     * @param array|Component $value
     *
     * @return $this
     */
    public function addSlot(array|Component $value): static
    {
        $this->attributes['slot'] = $value;

        return $this;
    }

    /**
     * @param bool|array|string $value
     *
     * @return $this
     */
    public function setPermission(bool|array|string $value): static
    {
        $this->attributes['permissions'] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function isNeedUpdate(): static
    {
        $this->attributes['needUpdate'] = true;

        return $this;
    }
}
