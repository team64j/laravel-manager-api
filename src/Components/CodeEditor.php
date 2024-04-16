<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class CodeEditor extends Component
{
    /**
     * @param string|null $model
     * @param string|null $label
     * @param string|null $help
     * @param string|null $class
     */
    public function __construct(
        string $model = null,
        string $label = null,
        string $help = null,
        string $class = null
    ) {
        $attributes = [
            'component' => 'AppCodeEditor',
            'attrs' => [
                'label' => $label,
                'help' => $help,
                'class' => $class,
                'config' => [
                    [
                        'component' => 'Codemirror',
                        'name' => 'Codemirror',
                    ],
                ],
            ],
            'model' => $model,
        ];

        parent::__construct($attributes);
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
     * @param int|string $value
     *
     * @return $this
     */
    public function setRows(int|string $value): static
    {
        $this->attributes['attrs']['rows'] = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLanguage(string $value): static
    {
        foreach ($this->attributes['attrs']['config'] as &$attr) {
            if ($attr['component'] == 'Codemirror') {
                $attr['lang'] = $value;
            }
        }

        return $this;
    }

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setConfig(array $value): static
    {
        $this->attributes['attrs']['config'] = $value;

        return $this;
    }
}
