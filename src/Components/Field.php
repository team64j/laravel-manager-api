<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Components;

use Illuminate\Support\Facades\Lang;

class Field extends Component
{
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
     * @param null $key
     * @param null $value
     * @param bool|null $selected
     *
     * @return $this
     */
    public function addOption($key, $value = null, bool $selected = null): static
    {
        $option = [
            'key' => $key,
            'value' => $value,
        ];

        if (is_null($value)) {
            $option['value'] = $key;
        }

        if (!is_null($selected)) {
            $option['selected'] = $selected;
        }

        $this->attributes['attrs']['data'][] = $option;

        return $this;
    }

    public function addYesNo(
        string $langYes = null,
        string $langNo = null,
        string | int $valueYes = 1,
        string | int $valueNo = 0): static
    {
        if (is_null($langYes)) {
            $langYes = Lang::get('global.yes');
        }

        if (is_null($langNo)) {
            $langNo = Lang::get('global.no');
        }

        return $this
            ->addOption($valueYes, $langYes)
            ->addOption($valueNo, $langNo);
    }

    /**
     * @param null $value
     *
     * @return $this
     */
    public function setValue($value = null): static
    {
        $this->attributes['attrs']['value'] = $value;

        return $this;
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
    public function setClass(string $value = null): static
    {
        $this->attributes['attrs']['class'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setLabel(string $value = null): static
    {
        $this->attributes['attrs']['label'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setDescription(string $value = null): static
    {
        $this->attributes['attrs']['description'] = $value;

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

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setInputClass(string $value = null): static
    {
        $this->attributes['attrs']['inputClass'] = $value;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setRequired(string $value = null): static
    {
        $this->attributes['attrs']['requiredText'] = $value;

        return $this;
    }

    /**
     * @param $true
     * @param $false
     *
     * @return $this
     */
    public function setCheckedValue($true = null, $false = null): static
    {
        $this->attributes['attrs']['trueValue'] = $true;
        $this->attributes['attrs']['falseValue'] = $false;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setUrl(string $value = null): static
    {
        $this->attributes['attrs']['url'] = $value;

        return $this;
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
    public function setNew(string $value): static
    {
        $this->attributes['attrs']['itemNew'] = $value;

        if (!empty($this->attributes['attrs']['url'])) {
            $this->attributes['attrs']['url'] .= stripos($this->attributes['attrs']['url'], '?') !== false ? '&' : '?';
            $this->attributes['attrs']['url'] .= 'itemNew=' . $value;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function isRequired(): static
    {
        $this->attributes['attrs']['required'] = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function isReadonly(): static
    {
        $this->attributes['attrs']['readonly'] = true;

        return $this;
    }
}
