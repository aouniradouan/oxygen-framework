<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Security\OxygenSecurityScanner;

/**
 * SecurityScanCommand - Full Security Audit
 * 
 * Usage: php oxygen security:scan [--type=all|sql|xss|csrf|files] [--fix]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class SecurityScanCommand extends Command
{
    /**
     * Command name
     * 
     * @var string
     */
    protected $name = 'security:scan';

    /**
     * Command description
     * 
     * @var string
     */
    protected $description = 'Run comprehensive security audit on the project';

    /**
     * Execute the command
     * 
     * @param array $args Command arguments
     * @return void
     */
    public function execute($args = [])
    {
        $this->info("🔒 Starting Security Scan...\n");

        // Parse options
        $type = $this->getOption($args, '--type', 'all');
        $fix = $this->hasOption($args, '--fix');

        // Configure scanner
        $config = [
            'mode' => 'balanced',
            'enabled_checks' => $this->getEnabledChecks($type),
        ];

        // Run scanner
        $scanner = new OxygenSecurityScanner($config);
        $projectPath = getcwd();

        $this->info("Scanning project: {$projectPath}");
        $this->info("Scan type: {$type}\n");

        $results = $scanner->scanProject($projectPath);

        // Display results
        $this->displayResults($results);

        // Generate report
        $this->generateReport($scanner, $results);

        // Auto-fix if requested
        if ($fix && $results['summary']['vulnerabilities'] > 0) {
            $this->info("\n🔧 Auto-fixing vulnerabilities...");
            $this->runAutoFix($results['vulnerabilities']);
        }

        // Exit code based on severity
        if ($results['summary']['critical'] > 0) {
            exit(1);
        }
    }

    /**
     * Get enabled checks based on type
     * 
     * @param string $type Scan type
     * @return array
     */
    protected function getEnabledChecks($type)
    {
        $allChecks = [
            'sql_injection' => true,
            'xss' => true,
            'csrf' => true,
            'file_upload' => true,
            'code_patterns' => true,
            'configuration' => true,
        ];

        if ($type === 'all') {
            return $allChecks;
        }

        // Enable specific check
        $checks = array_fill_keys(array_keys($allChecks), false);

        switch ($type) {
            case 'sql':
                $checks['sql_injection'] = true;
                break;
            case 'xss':
                $checks['xss'] = true;
                break;
            case 'csrf':
                $checks['csrf'] = true;
                break;
            case 'files':
                $checks['file_upload'] = true;
                break;
            case 'patterns':
                $checks['code_patterns'] = true;
                break;
            case 'config':
                $checks['configuration'] = true;
                break;
        }

        return $checks;
    }

    /**
     * Display scan results
     * 
     * @param array $results Scan results
     * @return void
     */
    protected function displayResults($results)
    {
        $summary = $results['summary'];

        $this->info("\n" . str_repeat("=", 60));
        $this->info("SECURITY SCAN RESULTS");
        $this->info(str_repeat("=", 60) . "\n");

        $this->info("Files Scanned: {$summary['scanned_files']}");
        $this->info("Total Vulnerabilities: {$summary['vulnerabilities']}\n");

        // Severity breakdown
        if ($summary['critical'] > 0) {
            $this->error("  Critical: {$summary['critical']}");
        }
        if ($summary['high'] > 0) {
            $this->warning("  High: {$summary['high']}");
        }
        if ($summary['medium'] > 0) {
            $this->warning("  Medium: {$summary['medium']}");
        }
        if ($summary['low'] > 0) {
            $this->info("  Low: {$summary['low']}");
        }

        if ($summary['vulnerabilities'] === 0) {
            $this->success("\n✓ No vulnerabilities detected! Your code is secure.");
            return;
        }

        // Display vulnerabilities
        $this->info("\n" . str_repeat("-", 60));
        $this->info("VULNERABILITIES FOUND");
        $this->info(str_repeat("-", 60) . "\n");

        foreach ($results['vulnerabilities'] as $index => $vuln) {
            $num = $index + 1;
            $severity = strtoupper($vuln['severity']);

            $this->displayVulnerability($num, $vuln);
        }
    }

    /**
     * Display single vulnerability
     * 
     * @param int $num Vulnerability number
     * @param array $vuln Vulnerability data
     * @return void
     */
    protected function displayVulnerability($num, $vuln)
    {
        $severityColor = $this->getSeverityColor($vuln['severity']);

        echo "\n{$num}. ";
        $this->colorize("[{$vuln['severity']}]", $severityColor);
        echo " {$vuln['type']} in {$vuln['file']}:{$vuln['line']}\n";
        echo "   {$vuln['message']}\n";

        if (isset($vuln['code_snippet'])) {
            echo "\n   Code:\n";
            $lines = explode("\n", $vuln['code_snippet']);
            foreach ($lines as $line) {
                echo "   " . $line . "\n";
            }
        }
    }

    /**
     * Get severity color
     * 
     * @param string $severity Severity level
     * @return string Color code
     */
    protected function getSeverityColor($severity)
    {
        switch (strtolower($severity)) {
            case 'critical':
                return 'red';
            case 'high':
                return 'yellow';
            case 'medium':
                return 'cyan';
            case 'low':
                return 'green';
            default:
                return 'white';
        }
    }

    /**
     * Generate HTML report
     * 
     * @param OxygenSecurityScanner $scanner Scanner instance
     * @param array $results Scan results
     * @return void
     */
    protected function generateReport($scanner, $results)
    {
        $reportDir = getcwd() . '/storage/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $reportFile = $reportDir . '/security-scan-' . date('Y-m-d_H-i-s') . '.html';
        $html = $scanner->generateHtmlReport();

        file_put_contents($reportFile, $html);

        $this->info("\n📄 HTML report generated: {$reportFile}");
    }

    /**
     * Run auto-fix
     * 
     * @param array $vulnerabilities Vulnerabilities to fix
     * @return void
     */
    protected function runAutoFix($vulnerabilities)
    {
        require_once getcwd() . '/app/Core/Security/OxygenSecurityFixer.php';

        $fixer = new \Oxygen\Core\Security\OxygenSecurityFixer(false, getcwd() . '/storage/backups/security');
        $results = $fixer->fixVulnerabilities($vulnerabilities, true);

        $this->info("\nFix Results:");
        $this->info("  Files Fixed: {$results['summary']['files_fixed']}");
        $this->info("  Issues Fixed: {$results['summary']['issues_fixed']}");
        $this->info("  Backups Created: {$results['summary']['backups_created']}");

        if ($results['summary']['files_fixed'] > 0) {
            $this->success("\n✓ Auto-fix completed successfully!");
        }
    }

    /**
     * Get option value from arguments
     * 
     * @param array $args Arguments
     * @param string $option Option name
     * @param mixed $default Default value
     * @return mixed
     */
    protected function getOption($args, $option, $default = null)
    {
        foreach ($args as $arg) {
            if (strpos($arg, $option . '=') === 0) {
                return substr($arg, strlen($option) + 1);
            }
        }
        return $default;
    }

    /**
     * Check if option exists
     * 
     * @param array $args Arguments
     * @param string $option Option name
     * @return bool
     */
    protected function hasOption($args, $option)
    {
        return in_array($option, $args);
    }

    /**
     * Colorize text
     * 
     * @param string $text Text to colorize
     * @param string $color Color name
     * @return void
     */
    protected function colorize($text, $color)
    {
        $colors = [
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'cyan' => "\033[36m",
            'white' => "\033[37m",
            'reset' => "\033[0m",
        ];

        echo ($colors[$color] ?? '') . $text . $colors['reset'];
    }
}
