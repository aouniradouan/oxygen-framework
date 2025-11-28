<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Scanner Mode
    |--------------------------------------------------------------------------
    |
    | Available modes: strict, balanced, permissive
    |
    | - strict: Blocks potentially dangerous patterns (may have false positives)
    | - balanced: Warns about suspicious patterns but allows execution
    | - permissive: Only logs security concerns
    |
    */
    'mode' => env('SECURITY_MODE', 'permissive'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Security Checks
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific security checks
    |
    */
    'enabled_checks' => [
        'sql_injection' => true,
        'xss' => true,
        'csrf' => true,
        'file_upload' => true,
        'code_patterns' => false,
        'configuration' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | Paths to exclude from security scanning
    |
    */
    'exclude_paths' => [
        'vendor',
        'storage/cache',
        'storage/logs',
        'node_modules',
        'config',
        'app',
        'routes',
        'database',
        '.git'
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitelist Patterns
    |--------------------------------------------------------------------------
    |
    | Regex patterns that should be whitelisted (not flagged as vulnerabilities)
    |
    */
    'whitelist_patterns' => [
        // Add custom whitelist patterns here
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Fix Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic security fixes
    |
    */
    'auto_fix' => [
        'enabled' => true,
        'create_backup' => true,
        'backup_dir' => __DIR__ . '/../storage/backups/security',
    ],

    /*
    |--------------------------------------------------------------------------
    | Virus Scanner Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for virus and malware detection
    |
    */
    'virus_scanner' => [
        'quarantine_dir' => __DIR__ . '/../storage/quarantine',
        'auto_quarantine' => false,
        'deep_scan_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Reports
    |--------------------------------------------------------------------------
    |
    | Configuration for security report generation
    |
    */
    'reports' => [
        'output_dir' => __DIR__ . '/../storage/reports',
        'format' => 'html', // html, json, pdf
        'include_code_snippets' => true,
    ]
];
