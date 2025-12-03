<?php

namespace Oxygen\Core\Security;

/**
 * OxygenVirusScanner - Malware and Virus Detection System
 * 
 * Scans files for malicious code patterns, suspicious functions,
 * and known malware signatures.
 * 
 * Compatible with PHP 7.4 - 8.4
 * 
 * @package    Oxygen\Core\Security
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class OxygenVirusScanner
{
    /**
     * Scan results
     * 
     * @var array
     */
    protected $results = [];

    /**
     * Quarantine directory
     * 
     * @var string
     */
    protected $quarantineDir;

    /**
     * Virus signatures database
     * 
     * @var array
     */
    protected $signatures = [];

    /**
     * Constructor
     * 
     * @param string $quarantineDir Quarantine directory path
     */
    public function __construct($quarantineDir = null)
    {
        $this->quarantineDir = $quarantineDir ?? getcwd() . '/storage/quarantine';
        $this->loadSignatures();
        $this->initializeResults();
    }

    /**
     * Initialize results structure
     * 
     * @return void
     */
    protected function initializeResults()
    {
        $this->results = [
            'summary' => [
                'total_files' => 0,
                'scanned_files' => 0,
                'infected_files' => 0,
                'suspicious_files' => 0,
                'quarantined_files' => 0,
            ],
            'threats' => [],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Load virus signatures
     * 
     * @return void
     */
    protected function loadSignatures()
    {
        $signatureFile = getcwd() . '/storage/security/virus_signatures.json';

        if (file_exists($signatureFile)) {
            $this->signatures = json_decode(file_get_contents($signatureFile), true) ?? [];
        } else {
            // Default signatures
            $this->signatures = $this->getDefaultSignatures();
        }
    }

    /**
     * Get default virus signatures
     * 
     * @return array
     */
    protected function getDefaultSignatures()
    {
        return [
            'malicious_functions' => [
                'eval',
                'base64_decode',
                'gzinflate',
                'str_rot13',
                'gzuncompress',
                'assert',
                'create_function',
                'preg_replace.*\/e',
                'exec',
                'shell_exec',
                'system',
                'passthru',
                'proc_open',
                'popen',
                'curl_exec',
                'curl_multi_exec',
                'parse_ini_file',
                'show_source',
            ],
            'suspicious_patterns' => [
                // Obfuscated code
                '/\$\w+\s*=\s*base64_decode\s*\(/i',
                '/eval\s*\(\s*base64_decode/i',
                '/eval\s*\(\s*gzinflate/i',
                '/eval\s*\(\s*str_rot13/i',
                '/assert\s*\(\s*base64_decode/i',

                // Backdoors
                '/\$_(?:GET|POST|REQUEST|COOKIE)\s*\[\s*[\'"](?:cmd|command|exec|shell)[\'"]/',
                '/system\s*\(\s*\$_(?:GET|POST|REQUEST)/i',
                '/passthru\s*\(\s*\$_(?:GET|POST|REQUEST)/i',

                // File operations
                '/file_put_contents\s*\(.*?base64_decode/i',
                '/fwrite\s*\(.*?base64_decode/i',

                // Network operations
                '/fsockopen\s*\(/i',
                '/curl_init\s*\(\s*\$_(?:GET|POST|REQUEST)/i',

                // Webshells
                '/c99shell/i',
                '/r57shell/i',
                '/wso\s*shell/i',
                '/FilesMan/i',
                '/\$auth_pass/i',

                // Crypto miners
                '/coinhive/i',
                '/cryptonight/i',
                '/monero/i',

                // Suspicious encoding
                '/\\\\x[0-9a-f]{2}/i',
                '/chr\s*\(\s*\d+\s*\)\s*\.\s*chr/i',
            ],
            'known_malware_hashes' => [
                // MD5 hashes of known malware files
                // This would be populated from a malware database
            ],
        ];
    }

    /**
     * Scan project for viruses
     * 
     * @param string $path Project root path
     * @param bool $deepScan Enable deep scanning
     * @param bool $quarantine Automatically quarantine infected files
     * @return array Scan results
     */
    public function scanProject($path, $deepScan = false, $quarantine = false)
    {
        $this->initializeResults();

        $files = $this->getScannableFiles($path);
        $this->results['summary']['total_files'] = count($files);

        foreach ($files as $file) {
            $this->scanFile($file, $deepScan, $quarantine);
            $this->results['summary']['scanned_files']++;
        }

        return $this->results;
    }

    /**
     * Scan a single file
     * 
     * @param string $filePath File path
     * @param bool $deepScan Enable deep scanning
     * @param bool $quarantine Automatically quarantine if infected
     * @return array|null Scan result
     */
    public function scanFile($filePath, $deepScan = false, $quarantine = false)
    {
        $content = file_get_contents($filePath);
        $threats = [];

        // Check file hash against known malware
        $fileHash = md5($content);
        if (in_array($fileHash, $this->signatures['known_malware_hashes'])) {
            $threats[] = [
                'type' => 'Known Malware',
                'severity' => 'critical',
                'description' => 'File matches known malware signature',
            ];
        }

        // Check for malicious functions
        foreach ($this->signatures['malicious_functions'] as $function) {
            if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/i', $content)) {
                $threats[] = [
                    'type' => 'Malicious Function',
                    'severity' => 'high',
                    'description' => "Suspicious function detected: {$function}()",
                    'function' => $function,
                ];
            }
        }

        // Check for suspicious patterns
        foreach ($this->signatures['suspicious_patterns'] as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $threats[] = [
                    'type' => 'Suspicious Pattern',
                    'severity' => 'high',
                    'description' => 'Suspicious code pattern detected',
                    'pattern' => substr($matches[0], 0, 100),
                ];
            }
        }

        // Deep scan - more intensive checks
        if ($deepScan) {
            $threats = array_merge($threats, $this->deepScan($content, $filePath));
        }

        // Process threats
        if (!empty($threats)) {
            $relativePath = $this->getRelativePath($filePath);

            $threatInfo = [
                'file' => $relativePath,
                'full_path' => $filePath,
                'threats' => $threats,
                'threat_count' => count($threats),
                'file_size' => filesize($filePath),
                'file_hash' => $fileHash,
            ];

            $this->results['threats'][] = $threatInfo;
            $this->results['summary']['infected_files']++;

            // Quarantine if requested
            if ($quarantine) {
                $this->quarantineFile($filePath, $threatInfo);
            }

            return $threatInfo;
        }

        return null;
    }

    /**
     * Perform deep scan
     * 
     * @param string $content File content
     * @param string $filePath File path
     * @return array Additional threats found
     */
    protected function deepScan($content, $filePath)
    {
        $threats = [];

        // Check for heavily obfuscated code
        $obfuscationScore = $this->calculateObfuscationScore($content);
        if ($obfuscationScore > 0.7) {
            $threats[] = [
                'type' => 'Obfuscated Code',
                'severity' => 'medium',
                'description' => "High obfuscation score: {$obfuscationScore}",
            ];
        }

        // Check for unusual file permissions
        $perms = fileperms($filePath);
        if ($perms & 0x0002) { // World writable
            $threats[] = [
                'type' => 'Insecure Permissions',
                'severity' => 'medium',
                'description' => 'File is world-writable',
            ];
        }

        // Check for hidden PHP code in images
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $filePath)) {
            if (preg_match('/<\?php/i', $content)) {
                $threats[] = [
                    'type' => 'Hidden PHP Code',
                    'severity' => 'critical',
                    'description' => 'PHP code found in image file',
                ];
            }
        }

        // Check for IRC bot patterns
        if (preg_match('/PRIVMSG|NOTICE.*?#/i', $content)) {
            $threats[] = [
                'type' => 'IRC Bot',
                'severity' => 'critical',
                'description' => 'Potential IRC bot code detected',
            ];
        }

        // Check for mail spam patterns
        if (preg_match_all('/mail\s*\(/i', $content) > 5) {
            $threats[] = [
                'type' => 'Mail Spam',
                'severity' => 'high',
                'description' => 'Multiple mail() calls detected - potential spam script',
            ];
        }

        return $threats;
    }

    /**
     * Calculate obfuscation score
     * 
     * @param string $content File content
     * @return float Score between 0 and 1
     */
    protected function calculateObfuscationScore($content)
    {
        $score = 0;
        $checks = 0;

        // Check for base64 strings
        if (preg_match_all('/[A-Za-z0-9+\/]{50,}={0,2}/', $content) > 3) {
            $score += 0.3;
        }
        $checks++;

        // Check for hex strings
        if (preg_match_all('/\\\\x[0-9a-f]{2}/i', $content) > 10) {
            $score += 0.3;
        }
        $checks++;

        // Check for character concatenation
        if (preg_match_all('/chr\s*\(\s*\d+\s*\)/', $content) > 5) {
            $score += 0.2;
        }
        $checks++;

        // Check for variable variables
        if (preg_match_all('/\$\$\w+/', $content) > 3) {
            $score += 0.2;
        }
        $checks++;

        return min(1.0, $score);
    }

    /**
     * Check if path is critical to the framework
     * 
     * @param string $filePath File path
     * @return bool
     */
    public function isCriticalPath($filePath)
    {
        $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);
        $basePath = getcwd();

        $criticalPaths = [
            $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core',
            $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Console',
            $basePath . DIRECTORY_SEPARATOR . 'config',
            $basePath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php',
            $basePath . DIRECTORY_SEPARATOR . 'vendor',
            $basePath . DIRECTORY_SEPARATOR . 'composer.json',
            $basePath . DIRECTORY_SEPARATOR . 'composer.lock',
            $basePath . DIRECTORY_SEPARATOR . 'server.php',
        ];

        foreach ($criticalPaths as $critical) {
            if (strpos($normalizedPath, $critical) === 0 || $normalizedPath === $critical) {
                return true;
            }
        }

        return false;
    }

    /**
     * Quarantine infected file
     * 
     * @param string $filePath File path
     * @param array $threatInfo Threat information
     * @return bool Success
     */
    protected function quarantineFile($filePath, $threatInfo)
    {
        // STRICT SAFETY CHECK: Never quarantine critical files
        if ($this->isCriticalPath($filePath)) {
            return false;
        }

        // Create quarantine directory if it doesn't exist
        if (!is_dir($this->quarantineDir)) {
            mkdir($this->quarantineDir, 0755, true);
        }

        $filename = basename($filePath);
        $timestamp = date('Y-m-d_H-i-s');
        $quarantinePath = $this->quarantineDir . '/' . $timestamp . '_' . $filename;

        // Move file to quarantine
        if (rename($filePath, $quarantinePath)) {
            // Create info file with original path for restoration
            $threatInfo['original_path'] = $filePath;
            $threatInfo['quarantined_at'] = $timestamp;
            $threatInfo['reason'] = 'Virus Scan Detection';

            $infoPath = $quarantinePath . '.info.json';
            file_put_contents($infoPath, json_encode($threatInfo, JSON_PRETTY_PRINT));

            $this->results['summary']['quarantined_files']++;
            return true;
        }

        return false;
    }

    /**
     * Get scannable files
     * 
     * @param string $path Directory path
     * @return array
     */
    protected function getScannableFiles($path)
    {
        $files = [];
        $extensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'inc', 'suspected'];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());

                // Scan PHP files and suspicious files
                if (in_array($ext, $extensions)) {
                    $filePath = $file->getPathname();

                    // Skip vendor, cache, quarantine, and core framework directories
                    if (
                        strpos($filePath, 'vendor') === false &&
                        strpos($filePath, 'cache') === false &&
                        strpos($filePath, 'quarantine') === false &&
                        strpos($filePath, 'app' . DIRECTORY_SEPARATOR . 'Core') === false &&
                        strpos($filePath, 'app' . DIRECTORY_SEPARATOR . 'Console') === false &&
                        strpos($filePath, 'tests') === false
                    ) {
                        $files[] = $filePath;
                    }
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
    <title>Virus Scan Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #e74c3c; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { padding: 20px; border-radius: 5px; text-align: center; background: #f8f9fa; }
        .stat-box h3 { margin: 0; font-size: 32px; }
        .stat-box p { margin: 5px 0 0 0; color: #666; }
        .infected { background: #ffebee; border-left: 4px solid #c62828; }
        .threat { margin: 15px 0; padding: 15px; border-radius: 5px; background: #fff3e0; border-left: 4px solid #ef6c00; }
        .threat h4 { margin: 0 0 10px 0; color: #e65100; }
        .threat-item { margin: 10px 0; padding: 10px; background: #fff; border-left: 3px solid #ff9800; }
        .severity-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; color: white; }
        .severity-critical { background: #c62828; }
        .severity-high { background: #ef6c00; }
        .severity-medium { background: #f9a825; }
        .clean { color: green; font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🦠 Virus Scan Report</h1>
        <p><strong>Scan Date:</strong> ' . $this->results['timestamp'] . '</p>
        
        <div class="summary">
            <div class="stat-box">
                <h3>' . $this->results['summary']['scanned_files'] . '</h3>
                <p>Files Scanned</p>
            </div>
            <div class="stat-box infected">
                <h3>' . $this->results['summary']['infected_files'] . '</h3>
                <p>Infected Files</p>
            </div>
            <div class="stat-box">
                <h3>' . $this->results['summary']['quarantined_files'] . '</h3>
                <p>Quarantined Files</p>
            </div>
        </div>

        <h2>Scan Results</h2>';

        if (empty($this->results['threats'])) {
            $html .= '<p class="clean">✓ No threats detected! Your system is clean.</p>';
        } else {
            foreach ($this->results['threats'] as $threat) {
                $html .= '
                <div class="threat">
                    <h4>🚨 ' . htmlspecialchars($threat['file']) . '</h4>
                    <p><strong>Threats Found:</strong> ' . $threat['threat_count'] . '</p>
                    <p><strong>File Hash:</strong> <code>' . $threat['file_hash'] . '</code></p>';

                foreach ($threat['threats'] as $t) {
                    $severityClass = strtolower($t['severity']);
                    $html .= '
                    <div class="threat-item">
                        <span class="severity-badge severity-' . $severityClass . '">' . strtoupper($t['severity']) . '</span>
                        <strong>' . htmlspecialchars($t['type']) . ':</strong> ' . htmlspecialchars($t['description']) . '
                    </div>';
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
