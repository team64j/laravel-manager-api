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
            'component' => 'CodeEditor',
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
     * @param int $value
     *
     * @return $this
     */
    public function setRows(int $value): static
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
}
