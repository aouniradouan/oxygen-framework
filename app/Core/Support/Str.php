<?php

namespace Oxygen\Core\Support;

class Str
{
    /**
     * Pluralize a word
     */
    public static function plural($value)
    {
        $value = trim($value);
        $lower = strtolower($value);

        // Uncountable words
        $uncountable = ['equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep', 'aircraft', 'bison', 'deer', 'moose', 'swine', 'means', 'offspring'];
        if (in_array($lower, $uncountable)) {
            return $value;
        }

        // Irregular words
        $irregular = [
            'person' => 'people',
            'man' => 'men',
            'child' => 'children',
            'sex' => 'sexes',
            'move' => 'moves',
            'foot' => 'feet',
            'goose' => 'geese',
            'tooth' => 'teeth',
            'quiz' => 'quizzes',
        ];

        if (isset($irregular[$lower])) {
            return $irregular[$lower];
        }

        // Rules
        if (substr($lower, -1) == 'y' && !in_array(substr($lower, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
            return substr($value, 0, -1) . 'ies';
        }

        if (substr($lower, -1) == 's' || substr($lower, -1) == 'x' || substr($lower, -1) == 'z' || substr($lower, -2) == 'ch' || substr($lower, -2) == 'sh') {
            return $value . 'es';
        }

        return $value . 's';
    }

    /**
     * Singularize a word
     */
    public static function singular($value)
    {
        $value = trim($value);
        $lower = strtolower($value);

        if (substr($lower, -3) == 'ies') {
            return substr($value, 0, -3) . 'y';
        }

        if (substr($lower, -2) == 'es' && !in_array(substr($lower, -3, 1), ['s', 'x', 'z', 'h'])) {
            return substr($value, 0, -2);
        }

        if (substr($lower, -1) == 's') {
            return substr($value, 0, -1);
        }

        return $value;
    }

    /**
     * Convert to snake_case
     */
    public static function snake($value)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }

    /**
     * Convert to StudlyCase
     */
    public static function studly($value)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}
