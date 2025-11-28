<?php

namespace Oxygen\Console\Commands\Generator\Questions;

/**
 * QuestionEngine - Intelligent Question System
 * 
 * Handles smart, context-aware questions with validation,
 * suggestions, and dynamic flow based on user answers.
 * 
 * @package    Oxygen\Console\Commands\Generator\Questions
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class QuestionEngine
{
    /**
     * The command instance for I/O
     */
    protected $command;

    /**
     * Context from previous answers
     */
    protected $context = [];

    /**
     * All answers collected
     */
    protected $answers = [];

    /**
     * Common field patterns
     */
    protected $fieldPatterns = [
        'user' => ['name', 'email', 'password', 'avatar', 'bio', 'phone'],
        'post' => ['title', 'slug', 'content', 'excerpt', 'featured_image', 'published_at', 'user_id'],
        'product' => ['name', 'description', 'price', 'stock', 'sku', 'category_id', 'images'],
        'category' => ['name', 'slug', 'description', 'parent_id'],
        'comment' => ['content', 'user_id', 'commentable_id', 'commentable_type', 'approved'],
        'order' => ['user_id', 'total', 'status', 'payment_method', 'shipping_address'],
    ];

    /**
     * Field type mappings
     */
    protected $fieldTypes = [
        'name' => 'string',
        'title' => 'string',
        'email' => 'string',
        'password' => 'string',
        'content' => 'text',
        'description' => 'text',
        'bio' => 'text',
        'price' => 'decimal',
        'stock' => 'integer',
        'quantity' => 'integer',
        'published_at' => 'timestamp',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'approved' => 'boolean',
        'avatar' => 'file',
        'image' => 'file',
        'images' => 'file',
        'featured_image' => 'file',
        'slug' => 'string',
        'status' => 'enum',
    ];

    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Ask a text question
     */
    public function ask($question, $default = null)
    {
        $answer = $this->command->ask($question, $default);
        return $answer;
    }

    /**
     * Ask a yes/no question
     */
    public function confirm($question, $default = true)
    {
        return $this->command->confirm($question, $default);
    }

    /**
     * Ask a choice question
     */
    public function choice($question, array $choices, $default = null)
    {
        $this->command->info($question);

        foreach ($choices as $index => $choice) {
            $this->command->line("  " . ($index + 1) . ". " . $choice);
        }

        $answer = $this->command->ask("Select option (1-" . count($choices) . ")", $default);
        $index = (int) $answer - 1;

        return isset($choices[$index]) ? $choices[$index] : $choices[0];
    }

    /**
     * Ask multiple choice question
     */
    public function multiChoice($question, array $choices)
    {
        $this->command->info($question);
        $this->command->line("  (Enter numbers separated by commas, e.g., 1,3,5)");

        foreach ($choices as $index => $choice) {
            $this->command->line("  " . ($index + 1) . ". " . $choice);
        }

        $answer = $this->command->ask("Select options");
        $selected = array_map('trim', explode(',', $answer));

        $results = [];
        foreach ($selected as $num) {
            $index = (int) $num - 1;
            if (isset($choices[$index])) {
                $results[] = $choices[$index];
            }
        }

        return $results;
    }

    /**
     * Suggest fields for a resource
     */
    public function suggestFields($resourceName)
    {
        $name = strtolower($resourceName);

        // Check for exact match
        if (isset($this->fieldPatterns[$name])) {
            return $this->fieldPatterns[$name];
        }

        // Check for partial match
        foreach ($this->fieldPatterns as $pattern => $fields) {
            if (strpos($name, $pattern) !== false) {
                return $fields;
            }
        }

        // Default fields
        return ['name', 'description'];
    }

    /**
     * Detect field type from field name
     */
    public function detectFieldType($fieldName)
    {
        $name = strtolower($fieldName);

        // Check exact match
        if (isset($this->fieldTypes[$name])) {
            return $this->fieldTypes[$name];
        }

        // Check for patterns
        if (strpos($name, '_id') !== false) {
            return 'foreignKey';
        }

        if (strpos($name, 'is_') === 0 || strpos($name, 'has_') === 0) {
            return 'boolean';
        }

        if (strpos($name, '_at') !== false) {
            return 'timestamp';
        }

        if (strpos($name, 'image') !== false || strpos($name, 'photo') !== false || strpos($name, 'file') !== false) {
            return 'file';
        }

        if (strpos($name, 'price') !== false || strpos($name, 'amount') !== false) {
            return 'decimal';
        }

        if (strpos($name, 'count') !== false || strpos($name, 'quantity') !== false || strpos($name, 'stock') !== false) {
            return 'integer';
        }

        // Default
        return 'string';
    }

    /**
     * Detect relationships from field names
     */
    public function detectRelationships(array $resources)
    {
        $relationships = [];

        foreach ($resources as $resource) {
            $resourceName = $resource['name'];
            $fields = $resource['fields'];

            foreach ($fields as $field) {
                // Check for foreign keys
                if (strpos($field['name'], '_id') !== false) {
                    $relatedModel = str_replace('_id', '', $field['name']);
                    $relatedModel = ucfirst($relatedModel);

                    // Check if related model exists in resources
                    $relatedExists = false;
                    foreach ($resources as $r) {
                        if (strtolower($r['name']) === strtolower($relatedModel)) {
                            $relatedExists = true;
                            break;
                        }
                    }

                    if ($relatedExists) {
                        // BelongsTo relationship
                        $relationships[] = [
                            'from' => $resourceName,
                            'to' => $relatedModel,
                            'type' => 'belongsTo',
                            'foreignKey' => $field['name'],
                            'method' => strtolower($relatedModel)
                        ];

                        // Inverse HasMany relationship
                        $relationships[] = [
                            'from' => $relatedModel,
                            'to' => $resourceName,
                            'type' => 'hasMany',
                            'foreignKey' => $field['name'],
                            'method' => strtolower($resourceName) . 's'
                        ];
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * Suggest validation rules for a field
     */
    public function suggestValidation($fieldName, $fieldType)
    {
        $rules = ['required'];

        switch ($fieldType) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'text':
                $rules[] = 'string';
                break;
            case 'email':
                $rules = ['required', 'email', 'unique:users,email'];
                break;
            case 'password':
                $rules = ['required', 'string', 'min:8'];
                break;
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'decimal':
                $rules[] = 'numeric';
                break;
            case 'boolean':
                $rules = ['boolean'];
                break;
            case 'file':
                $rules = ['file', 'max:5120']; // 5MB
                break;
            case 'foreignKey':
                $rules[] = 'integer';
                $rules[] = 'exists:' . str_replace('_id', 's', $fieldName) . ',id';
                break;
        }

        return implode('|', $rules);
    }

    /**
     * Store answer in context
     */
    public function remember($key, $value)
    {
        $this->context[$key] = $value;
        $this->answers[$key] = $value;
    }

    /**
     * Get answer from context
     */
    public function recall($key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Get all answers
     */
    public function getAllAnswers()
    {
        return $this->answers;
    }

    /**
     * Display a header
     */
    public function header($text)
    {
        $this->command->line('');
        $this->command->info('=== ' . $text . ' ===');
    }

    /**
     * Display success message
     */
    public function success($text)
    {
        $this->command->success('✓ ' . $text);
    }

    /**
     * Display info message
     */
    public function info($text)
    {
        $this->command->info($text);
    }

    /**
     * Display warning message
     */
    public function warning($text)
    {
        $this->command->line('<fg=yellow>⚠ ' . $text . '</>');
    }

    /**
     * Display error message
     */
    public function error($text)
    {
        $this->command->error('✗ ' . $text);
    }

    /**
     * Display a list
     */
    public function listing(array $items)
    {
        foreach ($items as $item) {
            $this->command->line('  - ' . $item);
        }
    }

    /**
     * Display a table
     */
    public function table(array $headers, array $rows)
    {
        $this->command->line('');

        // Simple table display
        $headerLine = '| ' . implode(' | ', $headers) . ' |';
        $separator = '+' . str_repeat('-', strlen($headerLine) - 2) . '+';

        $this->command->line($separator);
        $this->command->line($headerLine);
        $this->command->line($separator);

        foreach ($rows as $row) {
            $this->command->line('| ' . implode(' | ', $row) . ' |');
        }

        $this->command->line($separator);
    }
    /**
     * Display a line of text
     */
    public function line($text)
    {
        $this->command->line($text);
    }
}
