<?php

namespace Oxygen\Core\Security;

/**
 * OxygenSecurityFixer - Automated Security Issue Resolution
 * 
 * Automatically fixes common security vulnerabilities including:
 * - Input sanitization
 * - CSRF protection
 * - SQL injection prevention
 * - XSS protection
 * 
 * Compatible with PHP 7.4 - 8.4
 * 
 * @package    Oxygen\Core\Security
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class OxygenSecurityFixer
{
    /**
     * Backup directory
     * 
     * @var string
     */
    protected $backupDir;

    /**
     * Fix results
     * 
     * @var array
     */
    protected $results = [];

    /**
     * Dry run mode
     * 
     * @var bool
     */
    protected $dryRun = false;

    /**
     * Constructor
     * 
     * @param bool $dryRun Dry run mode (don't actually fix)
     * @param string $backupDir Backup directory
     */
    public function __construct($dryRun = false, $backupDir = null)
    {
        $this->dryRun = $dryRun;
        $this->backupDir = $backupDir ?? getcwd() . '/storage/backups/security';
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
                'files_processed' => 0,
                'files_fixed' => 0,
                'issues_fixed' => 0,
                'backups_created' => 0,
            ],
            'fixes' => [],
            'timestamp' => date('Y-m-d H:i:s'),
            'dry_run' => $this->dryRun,
        ];
    }

    /**
     * Fix security issues in vulnerabilities array
     * 
     * @param array $vulnerabilities Vulnerabilities from scanner
     * @param bool $createBackup Create backup before fixing
     * @return array Fix results
     */
    public function fixVulnerabilities($vulnerabilities, $createBackup = true)
    {
        $this->initializeResults();

        // Group vulnerabilities by file
        $fileVulns = [];
        foreach ($vulnerabilities as $vuln) {
            $file = $vuln['file'];
            if (!isset($fileVulns[$file])) {
                $fileVulns[$file] = [];
            }
            $fileVulns[$file][] = $vuln;
        }

        // Fix each file
        foreach ($fileVulns as $file => $vulns) {
            $this->fixFile($file, $vulns, $createBackup);
        }

        return $this->results;
    }

    /**
     * Fix security issues in a single file
     * 
     * @param string $filePath File path
     * @param array $vulnerabilities Vulnerabilities in this file
     * @param bool $createBackup Create backup before fixing
     * @return bool Success
     */
    protected function fixFile($filePath, $vulnerabilities, $createBackup = true)
    {
        $fullPath = getcwd() . '/' . $filePath;

        if (!file_exists($fullPath)) {
            return false;
        }

        $content = file_get_contents($fullPath);
        $originalContent = $content;
        $fixesApplied = [];

        // Create backup if requested
        if ($createBackup && !$this->dryRun) {
            $this->createBackup($fullPath);
        }

        // Apply fixes for each vulnerability
        foreach ($vulnerabilities as $vuln) {
            $fix = $this->applyFix($content, $vuln);
            if ($fix['applied']) {
                $content = $fix['content'];
                $fixesApplied[] = $fix['description'];
                $this->results['summary']['issues_fixed']++;
            }
        }

        // Write fixed content
        if (!empty($fixesApplied) && !$this->dryRun) {
            file_put_contents($fullPath, $content);
            $this->results['summary']['files_fixed']++;
        }

        $this->results['summary']['files_processed']++;

        if (!empty($fixesApplied)) {
            $this->results['fixes'][] = [
                'file' => $filePath,
                'fixes' => $fixesApplied,
                'fix_count' => count($fixesApplied),
            ];
        }

        return !empty($fixesApplied);
    }

    /**
     * Apply fix for a specific vulnerability
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function applyFix(&$content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        switch ($vulnerability['type']) {
            case 'SQL Injection':
                $result = $this->fixSQLInjection($content, $vulnerability);
                break;

            case 'XSS':
                $result = $this->fixXSS($content, $vulnerability);
                break;

            case 'CSRF':
                $result = $this->fixCSRF($content, $vulnerability);
                break;

            case 'Dangerous Code Pattern':
                $result = $this->fixDangerousPattern($content, $vulnerability);
                break;

            case 'Configuration':
                $result = $this->fixConfiguration($content, $vulnerability);
                break;
        }

        return $result;
    }

    /**
     * Fix SQL injection vulnerability
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function fixSQLInjection($content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        // Fix direct SQL concatenation by adding comment
        if (preg_match('/\$\w+\s*=\s*["\']SELECT\s+.*?\$\w+.*?["\']/i', $content, $matches)) {
            $comment = "// WARNING: SQL injection vulnerability - use prepared statements\n// Example: \$stmt = \$db->prepare('SELECT * FROM table WHERE id = ?');\n//          \$stmt->execute([\$id]);\n";

            $result['content'] = str_replace(
                $matches[0],
                $comment . $matches[0],
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Added warning comment for SQL injection vulnerability';
        }

        return $result;
    }

    /**
     * Fix XSS vulnerability
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function fixXSS($content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        // Fix unescaped echo statements
        if (preg_match('/echo\s+(\$\w+);/i', $content, $matches)) {
            $varName = $matches[1];
            $result['content'] = str_replace(
                $matches[0],
                "echo htmlspecialchars({$varName}, ENT_QUOTES, 'UTF-8');",
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Added htmlspecialchars() to prevent XSS';
        }

        // Fix direct $_GET, $_POST output
        if (preg_match('/echo\s+\$_(GET|POST|REQUEST)\[([^\]]+)\];/i', $content, $matches)) {
            $result['content'] = str_replace(
                $matches[0],
                "echo htmlspecialchars(\$_{$matches[1]}[{$matches[2]}], ENT_QUOTES, 'UTF-8');",
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Added htmlspecialchars() to user input output';
        }

        return $result;
    }

    /**
     * Fix CSRF vulnerability
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function fixCSRF($content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        // Add CSRF token to forms
        if (preg_match('/<form[^>]*method\s*=\s*["\']post["\']/i', $content, $matches)) {
            $csrfField = "\n    <input type=\"hidden\" name=\"csrf_token\" value=\"<?php echo \\Oxygen\\Core\\CSRF::generateToken(); ?>\">";

            $result['content'] = preg_replace(
                '/(<form[^>]*method\s*=\s*["\']post["\'][^>]*>)/i',
                "$1{$csrfField}",
                $content,
                1
            );
            $result['applied'] = true;
            $result['description'] = 'Added CSRF token to POST form';
        }

        return $result;
    }

    /**
     * Fix dangerous code pattern
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function fixDangerousPattern($content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        // Add warning comments for dangerous functions
        if (strpos($vulnerability['message'], 'eval()') !== false) {
            $result['content'] = str_replace(
                'eval(',
                "// SECURITY WARNING: eval() is extremely dangerous!\n// Consider refactoring this code.\neval(",
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Added warning comment for eval() usage';
        }

        // Fix weak password hashing
        if (preg_match('/(md5|sha1)\s*\(\s*\$.*?password.*?\)/i', $content, $matches)) {
            $result['content'] = str_replace(
                $matches[0],
                "password_hash(\$password, PASSWORD_DEFAULT)",
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Replaced weak hashing with password_hash()';
        }

        return $result;
    }

    /**
     * Fix configuration issue
     * 
     * @param string $content File content
     * @param array $vulnerability Vulnerability data
     * @return array Fix result
     */
    protected function fixConfiguration($content, $vulnerability)
    {
        $result = [
            'applied' => false,
            'content' => $content,
            'description' => '',
        ];

        // Fix debug mode
        if (strpos($vulnerability['message'], 'Debug mode') !== false) {
            $result['content'] = preg_replace(
                '/APP_DEBUG\s*=\s*true/i',
                'APP_DEBUG=false',
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Disabled debug mode';
        }

        // Fix display errors
        if (preg_match('/ini_set\s*\(\s*["\']display_errors["\']\s*,\s*["\']1["\']\s*\)/i', $content)) {
            $result['content'] = preg_replace(
                '/ini_set\s*\(\s*["\']display_errors["\']\s*,\s*["\']1["\']\s*\)/i',
                "ini_set('display_errors', '0')",
                $content
            );
            $result['applied'] = true;
            $result['description'] = 'Disabled display_errors';
        }

        return $result;
    }

    /**
     * Create backup of file
     * 
     * @param string $filePath File path
     * @return bool Success
     */
    protected function createBackup($filePath)
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $filename = basename($filePath);
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $this->backupDir . '/' . $timestamp . '_' . $filename;

        if (copy($filePath, $backupPath)) {
            $this->results['summary']['backups_created']++;
            return true;
        }

        return false;
    }

    /**
     * Get fix results
     * 
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Restore from backup
     * 
     * @param string $backupFile Backup filename
     * @param string $targetPath Target file path
     * @return bool Success
     */
    public function restoreFromBackup($backupFile, $targetPath)
    {
        $backupPath = $this->backupDir . '/' . $backupFile;

        if (!file_exists($backupPath)) {
            return false;
        }

        return copy($backupPath, $targetPath);
    }

    /**
     * List available backups
     * 
     * @return array
     */
    public function listBackups()
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $backups = [];
        $files = scandir($this->backupDir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $backups[] = [
                    'filename' => $file,
                    'path' => $this->backupDir . '/' . $file,
                    'size' => filesize($this->backupDir . '/' . $file),
                    'created' => filemtime($this->backupDir . '/' . $file),
                ];
            }
        }

        return $backups;
    }
}
