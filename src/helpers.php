<?php

if (!function_exists('api_url')) {
    function api_url($name = '', $parameters = [], $absolute = false): string
    {
        return str(route('manager.api' . ($name ? '.' . $name : ''), $parameters, $absolute))
            ->when(
                !$absolute,
                fn($str) => $str->replace(config('manager-api.uri') . '/', '')
            )
            ->toString();
    }
}