<?php

namespace Oxygen\Core;

/**
 * Validator - Simple validation system
 * 
 * Provides validation for request data with common rules.
 * 
 * @package    Oxygen\Core
 * @author     OxygenFramework
 * @version    2.1.0
 */
class Validator
{
    protected $data;
    protected $rules;
    protected $errors = [];
    protected $messages = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Validate the data
     */
    public function validate()
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        return true;
    }

    /**
     * Apply a single validation rule
     */
    protected function applyRule($field, $value, $rule)
    {
        // Parse rule with parameters (e.g., "max:255")
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $method = 'validate' . ucfirst($rule);

        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $params)) {
                $this->addError($field, $rule, $params);
            }
        }
    }

    /**
     * Validation Rules
     */
    protected function validateRequired($field, $value, $params)
    {
        return !empty($value) || $value === '0' || $value === 0;
    }

    protected function validateString($field, $value, $params)
    {
        return is_string($value);
    }

    protected function validateInteger($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateNumeric($field, $value, $params)
    {
        return is_numeric($value);
    }

    protected function validateEmail($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateUrl($field, $value, $params)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateMin($field, $value, $params)
    {
        $min = $params[0] ?? 0;

        if (is_numeric($value)) {
            return $value >= $min;
        }

        return strlen($value) >= $min;
    }

    protected function validateMax($field, $value, $params)
    {
        $max = $params[0] ?? 0;

        if (is_numeric($value)) {
            return $value <= $max;
        }

        return strlen($value) <= $max;
    }

    protected function validateIn($field, $value, $params)
    {
        return in_array($value, $params);
    }

    protected function validateBoolean($field, $value, $params)
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    protected function validateDate($field, $value, $params)
    {
        return strtotime($value) !== false;
    }

    protected function validateRegex($field, $value, $params)
    {
        $pattern = $params[0] ?? '';
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Add an error message
     */
    protected function addError($field, $rule, $params)
    {
        $message = $this->messages["{$field}.{$rule}"]
            ?? $this->getDefaultMessage($field, $rule, $params);

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Get default error message
     */
    protected function getDefaultMessage($field, $rule, $params)
    {
        $field = ucfirst(str_replace('_', ' ', $field));

        $messages = [
            'required' => "{$field} is required.",
            'string' => "{$field} must be a string.",
            'integer' => "{$field} must be an integer.",
            'numeric' => "{$field} must be numeric.",
            'email' => "{$field} must be a valid email address.",
            'url' => "{$field} must be a valid URL.",
            'min' => "{$field} must be at least {$params[0]}.",
            'max' => "{$field} must not exceed {$params[0]}.",
            'in' => "{$field} must be one of: " . implode(', ', $params),
            'boolean' => "{$field} must be true or false.",
            'date' => "{$field} must be a valid date.",
            'regex' => "{$field} format is invalid.",
        ];

        return $messages[$rule] ?? "{$field} is invalid.";
    }

    /**
     * Get all errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Check if validation failed
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Get validated data
     */
    public function validated()
    {
        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }

    /**
     * Static helper for quick validation
     */
    public static function make(array $data, array $rules, array $messages = [])
    {
        return new static($data, $rules, $messages);
    }
}
