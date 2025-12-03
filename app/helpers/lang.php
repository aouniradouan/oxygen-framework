<?php

use Oxygen\Core\Application;
use Oxygen\Core\Lang;

if (!function_exists('__')) {
    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string|null  $locale
     * @return string
     */
    function __($key, $replace = [], $locale = null)
    {
        return Application::getInstance()->make(Lang::class)->get($key, $replace, $locale);
    }
}

if (!function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string|null  $locale
     * @return string
     */
    function trans($key, $replace = [], $locale = null)
    {
        return __($key, $replace, $locale);
    }
}
