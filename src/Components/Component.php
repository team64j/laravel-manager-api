<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

use Closure;
use Illuminate\Support\Fluent;

abstract class Component extends Fluent
{
    /**
     * @return static
     */
    public static function make(): static
    {
        return new static(...func_get_args());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->clearAttributes();

        return parent::toArray();
    }

    /**
     * @return void
     */
    protected function clearAttributes(): void
    {
        if (!empty($this->attributes['attrs'])) {
            $this->attributes['attrs'] = array_filter($this->attributes['attrs'], fn($i) => !is_null($i));
        }

        $this->attributes = array_filter($this->attributes, fn($i) => !is_null($i));
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return static
     */
    public static function __callStatic($method, $parameters): static
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * @param mixed $cond
     * @param callable|null $callableIf
     * @param callable|null $callableElse
     *
     * @return $this
     */
    public function when(mixed $cond, callable $callableIf = null, callable $callableElse = null): static
    {
        if ($cond && $callableIf instanceof Closure) {
            return $callableIf($this);
        }

        if ($callableElse instanceof Closure) {
            return $callableElse($this);
        }

        return $this;
    }
}
