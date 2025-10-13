<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Traits;

use DateTimeInterface;
use Illuminate\Support\Facades\Config;

trait TimeMutatorTrait
{
    protected function convertDateTime(int | string | null $value = null, ?string $default = null): string
    {
        if (!$value) {
            $value = $default;
        }

        if (!$value) {
            return '';
        }

        return $this->asDateTime($value)->format('Y-m-d H:i:s');
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        $format = str_replace(
            ['dd', 'mm', 'YYYY',],
            ['d', 'm', 'Y',],
            Config::get('global.datetime_format', 'Y-m-d')
        );

        return $date->getTimestamp() ? $date->format($format . ' H:i:s') : '';
    }
}
