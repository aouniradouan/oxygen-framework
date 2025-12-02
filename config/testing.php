<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Test Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic test generation
    |
    */
    'generation' => [
        'ai_enabled' => env('TEST_AI_ENABLED', false),
        'generate_edge_cases' => true,
        'generate_boundary_tests' => true,
        'generate_security_tests' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Execution Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for test execution
    |
    */
    'execution' => [
        'parallel_enabled' => false,
        'timeout' => 300, // seconds
        'stop_on_failure' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Coverage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for code coverage analysis
    |
    */
    'coverage' => [
        'enabled' => true,
        'output_dir' => __DIR__ . '/../storage/coverage',
        'format' => 'html', // html, xml, text
        'min_threshold' => 70, // minimum coverage percentage
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Directories
    |--------------------------------------------------------------------------
    |
    | Directories for different test types
    |
    */
    'directories' => [
        'unit' => __DIR__ . '/../tests/Unit',
        'integration' => __DIR__ . '/../tests/Integration',
        'security' => __DIR__ . '/../tests/Security',
    ],

    /*
    |--------------------------------------------------------------------------
    | PHPUnit Configuration
    |--------------------------------------------------------------------------
    |
    | Path to PHPUnit executable and configuration
    |
    */
    'phpunit' => [
        'executable' => __DIR__ . '/../vendor/bin/phpunit',
        'config' => __DIR__ . '/../phpunit.xml',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Reports
    |--------------------------------------------------------------------------
    |
    | Configuration for test report generation
    |
    */
    'reports' => [
        'output_dir' => __DIR__ . '/../storage/reports',
        'format' => 'html', // html, xml, json
        'include_coverage' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered test generation
    |
    */
    'ai' => [
        'provider' => env('AI_PROVIDER', 'python'), // python, openai, claude
        'model' => env('AI_MODEL', 'default'),
        'max_scenarios_per_method' => 5,
    ],
];
