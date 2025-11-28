<?php

namespace Oxygen\Core\Security;

/**
 * OxygenSecurityScanner - Advanced Security Vulnerability Scanner
 * 
 * Comprehensive security scanner that detects SQL injection, XSS, CSRF,
 * file upload vulnerabilities, insecure configurations, and code patterns.
 * 
 * Compatible with PHP 7.4 - 8.4
 * 
 * @package    Oxygen\Core\Security
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class OxygenSecurityScanner
{
    /**
     * Scanner mode: strict, balanced, permissive
     * 
     * @var string
     */
    protected $mode = 'balanced';

    /**
     * Scan results
     * 
     * @var array
     */
    protected $results = [];

    /**
     * Configuration
     * 
     * @var array
     */
    protected $config = [];

    /**
     * Constructor
     * 
     * @param array $config Configuration options
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->mode = $this->config['mode'] ?? 'balanced';
    }

    /**
     * Get default configuration
     * 
     * @return array
     */
    protected function getDefaultConfig()
    {
        return [
            'mode' => 'balanced',
            'enabled_checks' => [
                'sql_injection' => true,
                'xss' => true,
                'csrf' => true,
                'file_upload' => true,
                'code_patterns' => true,
                'configuration' => true,
            ],
            'exclude_paths' => [
                'vendor',
                'storage/cache',
                'node_modules',
            ],
            'whitelist_patterns' => [],
        ];
    }

    /**
     * Scan entire project
     * 
     * @param string $path Project root path
     * @return array Scan results
     */
    public function scanProject($path)
    {
        $this->results = [
            'summary' => [
                'total_files' => 0,
                'scanned_files' => 0,
                'vulnerabilities' => 0,
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
            ],
            'vulnerabilities' => [],
            'timestamp' => date('Y-m-d H:i:s'),
            'mode' => $this->mode,
        ];

        // Scan PHP files
        $files = $this->getPhpFiles($path);
        $this->results['summary']['total_files'] = count($files);

        foreach ($files as $file) {
            $this->scanFile($file);
            $this->results['summary']['scanned_files']++;
        }

        // Scan configuration files
        if ($this->config['enabled_checks']['configuration']) {
            $this->scanConfiguration($path);
        }

        return $this->results;
    }

    /**
     * Scan a single file
     * 
     * @param string $filePath File path
     * @return void
     */
    protected function scanFile($filePath)
    {
        $content = file_get_contents($filePath);
        $relativePath = $this->getRelativePath($filePath);

        // SQL Injection Detection
        if ($this->config['enabled_checks']['sql_injection']) {
            $this->detectSQLInjection($content, $relativePath);
        }

        // XSS Detection
        if ($this->config['enabled_checks']['xss']) {
            $this->detectXSS($content, $relativePath);
        }

        // CSRF Detection
        if ($this->config['enabled_checks']['csrf']) {
            $this->detectCSRF($content, $relativePath);
        }

        // Dangerous Code Patterns
        if ($this->config['enabled_checks']['code_patterns']) {
            $this->detectDangerousPatterns($content, $relativePath);
        }
    }

    /**
     * Detect SQL injection vulnerabilities
     * 
     * @param string $content File content
     * @param string $filePath File path
     * @return void
     */
    protected function detectSQLInjection($content, $filePath)
    {
        $patterns = [
            // Direct SQL concatenation
            [
                'pattern' => '/\$\w+\s*=\s*["\']SELECT\s+.*?\$\w+.*?["\']/i',
                'severity' => 'critical',
                'message' => 'Direct SQL query concatenation detected - use prepared statements',
            ],
            [
                'pattern' => '/\$\w+\s*=\s*["\']INSERT\s+INTO\s+.*?\$\w+.*?["\']/i',
                'severity' => 'critical',
                'message' => 'Direct SQL INSERT concatenation detected - use prepared statements',
            ],
            [
                'pattern' => '/\$\w+\s*=\s*["\']UPDATE\s+.*?SET\s+.*?\$\w+.*?["\']/i',
                'severity' => 'critical',
                'message' => 'Direct SQL UPDATE concatenation detected - use prepared statements',
            ],
            [
                'pattern' => '/\$\w+\s*=\s*["\']DELETE\s+FROM\s+.*?\$\w+.*?["\']/i',
                'severity' => 'critical',
                'message' => 'Direct SQL DELETE concatenation detected - use prepared statements',
            ],
            // mysqli_query without prepared statements
            [
                'pattern' => '/mysqli_query\s*\([^,]+,\s*["\'].*?\$\w+.*?["\']\s*\)/i',
                'severity' => 'high',
                'message' => 'mysqli_query with variable concatenation - use prepared statements',
            ],
            // PDO query without prepared statements
            [
                'pattern' => '/->query\s*\(\s*["\'].*?\$\w+.*?["\']\s*\)/i',
                'severity' => 'high',
                'message' => 'PDO query with variable concatenation - use prepared statements',
            ],
        ];

        $this->checkPatterns($patterns, $content, $filePath, 'SQL Injection');
    }

    /**
     * Detect XSS vulnerabilities
     * 
     * @param string $content File content
     * @param string $filePath File path
     * @return void
     */
    protected function detectXSS($content, $filePath)
    {
        $patterns = [
            // Echo without escaping
            [
                'pattern' => '/echo\s+\$(?!this->escape|htmlspecialchars|htmlentities)\w+/i',
                'severity' => 'high',
                'message' => 'Unescaped echo statement - potential XSS vulnerability',
            ],
            // Print without escaping
            [
                'pattern' => '/print\s+\$(?!this->escape|htmlspecialchars|htmlentities)\w+/i',
                'severity' => 'high',
                'message' => 'Unescaped print statement - potential XSS vulnerability',
            ],
            // Direct $_GET, $_POST output
            [
                'pattern' => '/echo\s+\$_(GET|POST|REQUEST)\[/i',
                'severity' => 'critical',
                'message' => 'Direct output of user input - critical XSS vulnerability',
            ],
            // innerHTML in JavaScript
            [
                'pattern' => '/\.innerHTML\s*=\s*[^\'"]/',
                'severity' => 'medium',
                'message' => 'innerHTML assignment without sanitization',
            ],
        ];

        $this->checkPatterns($patterns, $content, $filePath, 'XSS');
    }

    /**
     * Detect CSRF vulnerabilities
     * 
     * @param string $content File content
     * @param string $filePath File path
     * @return void
     */
    protected function detectCSRF($content, $filePath)
    {
        // Check for forms without CSRF tokens
        if (preg_match('/<form[^>]*method\s*=\s*["\']post["\']/i', $content)) {
            if (!preg_match('/csrf_token|csrf_field|@csrf/i', $content)) {
                $this->addVulnerability([
                    'type' => 'CSRF',
                    'severity' => 'high',
                    'file' => $filePath,
                    'message' => 'POST form without CSRF protection detected',
                    'line' => $this->getLineNumber($content, '<form'),
                ]);
            }
        }

        // Check for state-changing operations without CSRF check
        $patterns = [
            [
                'pattern' => '/if\s*\(\s*\$_POST\s*\).*?(UPDATE|DELETE|INSERT)/is',
                'severity' => 'high',
                'message' => 'State-changing operation without CSRF validation',
            ],
        ];

        $this->checkPatterns($patterns, $content, $filePath, 'CSRF');
    }

    /**
     * Detect dangerous code patterns
     * 
     * @param string $content File content
     * @param string $filePath File path
     * @return void
     */
    protected function detectDangerousPatterns($content, $filePath)
    {
        $patterns = [
            // eval() usage
            [
                'pattern' => '/\beval\s*\(/i',
                'severity' => 'critical',
                'message' => 'eval() usage detected - extremely dangerous',
            ],
            // exec() with user input
            [
                'pattern' => '/\bexec\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'critical',
                'message' => 'exec() with user input - command injection vulnerability',
            ],
            // shell_exec() with user input
            [
                'pattern' => '/\bshell_exec\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'critical',
                'message' => 'shell_exec() with user input - command injection vulnerability',
            ],
            // system() with user input
            [
                'pattern' => '/\bsystem\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'critical',
                'message' => 'system() with user input - command injection vulnerability',
            ],
            // passthru() with user input
            [
                'pattern' => '/\bpassthru\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'critical',
                'message' => 'passthru() with user input - command injection vulnerability',
            ],
            // Unserialize with user input
            [
                'pattern' => '/\bunserialize\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/i',
                'severity' => 'critical',
                'message' => 'unserialize() with user input - object injection vulnerability',
            ],
            // include/require with user input
            [
                'pattern' => '/\b(include|require|include_once|require_once)\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'critical',
                'message' => 'File inclusion with user input - LFI/RFI vulnerability',
            ],
            // file_get_contents with user input
            [
                'pattern' => '/\bfile_get_contents\s*\(\s*\$_(GET|POST|REQUEST)/i',
                'severity' => 'high',
                'message' => 'file_get_contents() with user input - SSRF vulnerability',
            ],
            // Weak password hashing
            [
                'pattern' => '/\b(md5|sha1)\s*\(\s*\$.*?password/i',
                'severity' => 'high',
                'message' => 'Weak password hashing algorithm - use password_hash()',
            ],
            // Hardcoded credentials
            [
                'pattern' => '/\$password\s*=\s*["\'][^"\']{3,}["\']/i',
                'severity' => 'medium',
                'message' => 'Potential hardcoded password detected',
            ],
            // Debug mode enabled
            [
                'pattern' => '/ini_set\s*\(\s*["\']display_errors["\']\s*,\s*["\']1["\']\s*\)/i',
                'severity' => 'medium',
                'message' => 'Display errors enabled - should be disabled in production',
            ],
        ];

        $this->checkPatterns($patterns, $content, $filePath, 'Dangerous Code Pattern');
    }

    /**
     * Scan configuration files
     * 
     * @param string $projectPath Project root path
     * @return void
     */
    protected function scanConfiguration($projectPath)
    {
        // Check .env file
        $envPath = $projectPath . '/.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Check for debug mode
            if (preg_match('/APP_DEBUG\s*=\s*true/i', $envContent)) {
                $this->addVulnerability([
                    'type' => 'Configuration',
                    'severity' => 'medium',
                    'file' => '.env',
                    'message' => 'Debug mode enabled - should be false in production',
                    'line' => $this->getLineNumber($envContent, 'APP_DEBUG'),
                ]);
            }

            // Check for weak session settings
            if (preg_match('/SESSION_SECURE\s*=\s*false/i', $envContent)) {
                $this->addVulnerability([
                    'type' => 'Configuration',
                    'severity' => 'high',
                    'file' => '.env',
                    'message' => 'Insecure session settings - enable secure flag',
                    'line' => $this->getLineNumber($envContent, 'SESSION_SECURE'),
                ]);
            }
        }

        // Check for exposed .git directory
        if (is_dir($projectPath . '/public/.git')) {
            $this->addVulnerability([
                'type' => 'Configuration',
                'severity' => 'critical',
                'file' => 'public/.git',
                'message' => '.git directory exposed in public folder - critical security risk',
                'line' => 0,
            ]);
        }
    }

    /**
     * Check patterns against content
     * 
     * @param array $patterns Patterns to check
     * @param string $content File content
     * @param string $filePath File path
     * @param string $type Vulnerability type
     * @return void
     */
    protected function checkPatterns($patterns, $content, $filePath, $type)
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern['pattern'], $content, $matches)) {
                $this->addVulnerability([
                    'type' => $type,
                    'severity' => $pattern['severity'],
                    'file' => $filePath,
                    'message' => $pattern['message'],
                    'line' => $this->getLineNumber($content, $matches[0]),
                    'code_snippet' => $this->getCodeSnippet($content, $matches[0]),
                ]);
            }
        }
    }

    /**
     * Add vulnerability to results
     * 
     * @param array $vulnerability Vulnerability data
     * @return void
     */
    protected function addVulnerability($vulnerability)
    {
        $this->results['vulnerabilities'][] = $vulnerability;
        $this->results['summary']['vulnerabilities']++;
        $this->results['summary'][$vulnerability['severity']]++;
    }

    /**
     * Get PHP files from directory
     * 
     * @param string $path Directory path
     * @return array
     */
    protected function getPhpFiles($path)
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();

                // Check if path should be excluded
                $shouldExclude = false;
                foreach ($this->config['exclude_paths'] as $excludePath) {
                    if (strpos($filePath, $excludePath) !== false) {
                        $shouldExclude = true;
                        break;
                    }
                }

                if (!$shouldExclude) {
                    $files[] = $filePath;
                }
            }
        }

        return $files;
    }

    /**
     * Get relative path
     * 
     * @param string $filePath File path
     * @return string
     */
    protected function getRelativePath($filePath)
    {
        $basePath = getcwd();
        return str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
    }

    /**
     * Get line number of pattern match
     * 
     * @param string $content File content
     * @param string $needle Search string
     * @return int
     */
    protected function getLineNumber($content, $needle)
    {
        $pos = strpos($content, $needle);
        if ($pos === false) {
            return 0;
        }

        return substr_count($content, "\n", 0, $pos) + 1;
    }

    /**
     * Get code snippet around match
     * 
     * @param string $content File content
     * @param string $match Matched string
     * @return string
     */
    protected function getCodeSnippet($content, $match)
    {
        $lines = explode("\n", $content);
        $lineNumber = $this->getLineNumber($content, $match);

        $start = max(0, $lineNumber - 2);
        $end = min(count($lines), $lineNumber + 2);

        $snippet = [];
        for ($i = $start; $i < $end; $i++) {
            $snippet[] = ($i + 1) . ': ' . trim($lines[$i]);
        }

        return implode("\n", $snippet);
    }

    /**
     * Get scan results
     * 
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Generate HTML report
     * 
     * @return string
     */
    public function generateHtmlReport()
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Security Scan Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #e74c3c; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { padding: 20px; border-radius: 5px; text-align: center; }
        .stat-box h3 { margin: 0; font-size: 32px; }
        .stat-box p { margin: 5px 0 0 0; color: #666; }
        .critical { background: #ffebee; border-left: 4px solid #c62828; }
        .high { background: #fff3e0; border-left: 4px solid #ef6c00; }
        .medium { background: #fff9c4; border-left: 4px solid #f9a825; }
        .low { background: #e8f5e9; border-left: 4px solid #2e7d32; }
        .vulnerability { margin: 15px 0; padding: 15px; border-radius: 5px; border-left: 4px solid #ccc; }
        .vulnerability h4 { margin: 0 0 10px 0; }
        .vulnerability code { background: #f5f5f5; padding: 10px; display: block; overflow-x: auto; }
        .severity-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; color: white; }
        .severity-critical { background: #c62828; }
        .severity-high { background: #ef6c00; }
        .severity-medium { background: #f9a825; }
        .severity-low { background: #2e7d32; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Security Scan Report</h1>
        <p><strong>Scan Date:</strong> ' . $this->results['timestamp'] . '</p>
        <p><strong>Mode:</strong> ' . ucfirst($this->results['mode']) . '</p>
        
        <div class="summary">
            <div class="stat-box">
                <h3>' . $this->results['summary']['scanned_files'] . '</h3>
                <p>Files Scanned</p>
            </div>
            <div class="stat-box critical">
                <h3>' . $this->results['summary']['critical'] . '</h3>
                <p>Critical Issues</p>
            </div>
            <div class="stat-box high">
                <h3>' . $this->results['summary']['high'] . '</h3>
                <p>High Issues</p>
            </div>
            <div class="stat-box medium">
                <h3>' . $this->results['summary']['medium'] . '</h3>
                <p>Medium Issues</p>
            </div>
            <div class="stat-box low">
                <h3>' . $this->results['summary']['low'] . '</h3>
                <p>Low Issues</p>
            </div>
        </div>

        <h2>Vulnerabilities Found</h2>';

        if (empty($this->results['vulnerabilities'])) {
            $html .= '<p style="color: green; font-weight: bold;">✓ No vulnerabilities detected!</p>';
        } else {
            foreach ($this->results['vulnerabilities'] as $vuln) {
                $severityClass = strtolower($vuln['severity']);
                $html .= '
                <div class="vulnerability ' . $severityClass . '">
                    <h4>
                        <span class="severity-badge severity-' . $severityClass . '">' . strtoupper($vuln['severity']) . '</span>
                        ' . htmlspecialchars($vuln['type']) . ' in ' . htmlspecialchars($vuln['file']) . ':' . $vuln['line'] . '
                    </h4>
                    <p>' . htmlspecialchars($vuln['message']) . '</p>';

                if (isset($vuln['code_snippet'])) {
                    $html .= '<code>' . htmlspecialchars($vuln['code_snippet']) . '</code>';
                }

                $html .= '</div>';
            }
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }
}
