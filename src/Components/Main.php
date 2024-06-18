<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

class Main extends Component
{
    public function __construct($attributes = [])
    {
        $attributes = [
            'component' => 'AppMain',
            'attrs' => [
                'actions' => $attributes['actions'] ?? [],
                'title' => $attributes['title'] ?? [],
                'tabs' => $attributes['tabs'] ?? [],
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param array $actions
     *
     * @return $this
     */
    public function setActions(array $actions): static
    {
        $this->attributes['attrs']['actions'] = $actions;

        return $this;
    }

    /**
     * @param string $model
     * @param string|null $title
     * @param string|null $icon
     * @param int|null $id
     * @param string|null $help
     *
     * @return $this
     */
    public function setTitle(
        string $model,
        string $title = null,
        string $icon = null,
        int $id = null,
        string $help = null): static
    {
        $this->attributes['attrs']['title'] = Title::make()
            ->setModel($model)
            ->setTitle($title)
            ->setIcon($icon)
            ->setId($id)
            ->setHelp($help);

        return $this;
    }

    public function setTabs(array $tabs): static
    {
        $this->attributes['attrs']['tabs'] = $tabs;

        return $this;
    }
}
