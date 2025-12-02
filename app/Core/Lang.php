<?php

namespace Oxygen\Core;

class Lang
{
    protected static $locale = 'en';
    protected static $lines = [];

    /**
     * RTL (Right-to-Left) language codes
     * 
     * @var array
     */
    protected static $rtlLanguages = ['ar', 'he', 'fa', 'ur', 'yi', 'ji'];

    public static function setLocale($locale)
    {
        static::$locale = $locale;
    }

    public static function getLocale()
    {
        return static::$locale;
    }

    /**
     * Check if current locale is RTL (Right-to-Left)
     * 
     * @return bool
     */
    public static function isRTL()
    {
        return in_array(static::$locale, static::$rtlLanguages);
    }

    /**
     * Get text direction for current locale
     * 
     * @return string 'rtl' or 'ltr'
     */
    public static function getDirection()
    {
        return static::isRTL() ? 'rtl' : 'ltr';
    }

    /**
     * Get opposite direction (useful for CSS)
     * 
     * @return string
     */
    public static function getOppositeDirection()
    {
        return static::isRTL() ? 'ltr' : 'rtl';
    }

    public static function get($key, $replace = [])
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);

        static::load($file);

        $line = static::$lines[$file] ?? [];

        foreach ($parts as $part) {
            if (isset($line[$part])) {
                $line = $line[$part];
            } else {
                return $key;
            }
        }

        if (is_string($line)) {
            foreach ($replace as $key => $value) {
                $line = str_replace(':' . $key, $value, $line);
            }
            return $line;
        }

        return $key;
    }

    protected static function load($file)
    {
        if (isset(static::$lines[$file])) {
            return;
        }

        $path = __DIR__ . '/../../resources/lang/' . static::$locale . '/' . $file . '.php';

        if (file_exists($path)) {
            static::$lines[$file] = require $path;
        } else {
            static::$lines[$file] = [];
        }
    }
}
