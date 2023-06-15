<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * @method self setCancel(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setCancelTo(string $lang)
 * @method self setCancelTitle(string $to)
 * @method self setCancelClass(string $class)
 * @method self setCancelIcon(string $icon)
 * @method self setDelete(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setDeleteTo(string $lang)
 * @method self setDeleteTitle(string $to)
 * @method self setDeleteClass(string $class)
 * @method self setDeleteIcon(string $icon)
 * @method self setClear(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setClearTo(string $lang)
 * @method self setClearTitle(string $to)
 * @method self setClearClass(string $class)
 * @method self setClearIcon(string $icon)
 * @method self setRestore(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setRestoreTo(string $lang)
 * @method self setRestoreTitle(string $to)
 * @method self setRestoreClass(string $class)
 * @method self setRestoreIcon(string $icon)
 * @method self setCopy(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setCopyTo(string $lang)
 * @method self setCopyTitle(string $to)
 * @method self setCopyClass(string $class)
 * @method self setCopyIcon(string $icon)
 * @method self setView(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setViewTo(string $lang)
 * @method self setViewTitle(string $to)
 * @method self setViewClass(string $class)
 * @method self setViewIcon(string $icon)
 * @method self setNew(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setNewTo(string $lang)
 * @method self setNewTitle(string $to)
 * @method self setNewClass(string $class)
 * @method self setNewIcon(string $icon)
 * @method self setSave(string $lang = null, string $to = null, string $class = null, string $icon = null)
 * @method self setSaveTo(string $lang)
 * @method self setSaveTitle(string $to)
 * @method self setSaveClass(string $class)
 * @method self setSaveIcon(string $icon)
 * @method self setSaveAnd(string $lang = null, string $to = null, string $class = null, string $icon = null)
 */
class ActionsButtons extends Component
{
    /**
     * @param array $data
     * @param array $lang
     * @param array $to
     * @param array $classes
     * @param array $icon
     */
    public function __construct(
        array $data = [],
        array $lang = [],
        array $to = [],
        array $classes = [],
        array $icon = [])
    {
        $attributes = [
            'component' => 'EvoActionsButtons',
            'attrs' => [
                'data' => $data,
                'classes' => $classes,
                'icon' => $icon,
                'lang' => $lang,
                'to' => $to,
            ],
        ];

        parent::__construct($attributes);
    }

    /**
     * @param $method
     * @param $parameters
     *
     * @return $this
     */
    public function __call($method, $parameters): static
    {
        $str = Str::of($method);

        if ($str->test('/^set(.*?)Title$/')) {
            return $this->setActionTitle(
                $str->match('/^set(.*?)Title$/')->camel()->toString(),
                ...$parameters
            );
        }

        if ($str->test('/^set(.*?)To$/')) {
            return $this->setActionTo(
                $str->match('/^set(.*?)To$/')->camel()->toString(),
                ...$parameters
            );
        }

        if ($str->test('/^set(.*?)Class$/')) {
            return $this->setActionClass(
                $str->match('/^set(.*?)Class$/')->camel()->toString(),
                ...$parameters
            );
        }

        if ($str->test('/^set(.*?)Icon$/')) {
            return $this->setActionIcon(
                $str->match('/^set(.*?)Icon$/')->camel()->toString(),
                ...$parameters
            );
        }

        if ($str->test('/^set(.*?)$/')) {
            return $this->setAction(
                $str->match('/^set(.*?)$/')->camel()->toString(),
                ...$parameters
            );
        }

        return $this;
    }

    /**
     * @param $action
     * @param null $lang
     * @param null $to
     * @param null $class
     * @param null $icon
     *
     * @return $this
     */
    public function setAction($action, $lang = null, $to = null, $class = null, $icon = null): static
    {
        if (!in_array($action, $this->attributes['attrs']['data'])) {
            $this->attributes['attrs']['data'][] = $action;
        }

        !is_null($to) && $this->setActionTo($action, $to);

        !is_null($lang) && $this->setActionTitle($action, $lang);

        !is_null($class) && $this->setActionClass($action, $class);

        !is_null($icon) && $this->setActionIcon($action, $icon);

        return $this;
    }

    /**
     * @param $action
     * @param $lang
     *
     * @return $this
     */
    public function setActionTitle($action, $lang = null): static
    {
        $this->setAction($action);

        if (!isset($this->attributes['attrs']['lang'])) {
            $this->attributes['attrs']['lang'] = [];
        }

        if (is_null($lang)) {
            $lang = Lang::get('global.create_new');
        }

        if (!isset($this->attributes['attrs']['lang'][$action])) {
            $this->attributes['attrs']['lang'][$action] = $lang;
        }

        return $this;
    }

    /**
     * @param $action
     * @param null $to
     *
     * @return $this
     */
    public function setActionTo($action, $to = null): static
    {
        $this->setAction($action);

        if (!isset($this->attributes['attrs']['to'])) {
            $this->attributes['attrs']['to'] = [];
        }

        if (!is_null($to) && !isset($this->attributes['attrs']['to'][$action])) {
            if (is_array($to)) {
                $this->attributes['attrs']['to'][$action] = $to;
            } else {
                $this->attributes['attrs']['to'][$action] = [
                    'name' => $to,
                ];

                if ($action == 'new') {
                    $this->attributes['attrs']['to'][$action]['params']['id'] = 'new';
                }
            }
        }

        return $this;
    }

    /**
     * @param $action
     * @param null $class
     *
     * @return $this
     */
    public function setActionClass($action, $class = null): static
    {
        $this->setAction($action);

        if (!isset($this->attributes['attrs']['classes'])) {
            $this->attributes['attrs']['classes'] = [];
        }

        if (!is_null($class) && !isset($this->attributes['attrs']['classes'][$action])) {
            $this->attributes['attrs']['classes'][$action] = $class;
        }

        return $this;
    }

    /**
     * @param $action
     * @param null $icon
     *
     * @return $this
     */
    public function setActionIcon($action, $icon = null): static
    {
        $this->setAction($action);

        if (!isset($this->attributes['attrs']['icon'])) {
            $this->attributes['attrs']['icon'] = [];
        }

        if (!is_null($icon) && !isset($this->attributes['attrs']['icon'][$action])) {
            $this->attributes['attrs']['icon'][$action] = $icon;
        }

        return $this;
    }
}
