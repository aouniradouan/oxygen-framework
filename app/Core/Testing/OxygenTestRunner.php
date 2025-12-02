<?php

namespace Oxygen\Core\Testing;

/**
 * OxygenTestRunner - Advanced Test Execution Engine
 * 
 * Executes unit tests, integration tests, security tests with
 * parallel execution support and real-time progress reporting.
 * 
 * Compatible with PHP 7.4 - 8.4
 * 
 * @package    Oxygen\Core\Testing
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class OxygenTestRunner
{
    /**
     * Test results
     * 
     * @var array
     */
    protected $results = [];

    /**
     * PHPUnit executable path
     * 
     * @var string
     */
    protected $phpunitPath;

    /**
     * Project root path
     * 
     * @var string
     */
    protected $projectRoot;

    /**
     * Constructor
     * 
     * @param string $projectRoot Project root path
     */
    public function __construct($projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? getcwd();
        $this->phpunitPath = $this->findPhpUnit();
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
                'total_tests' => 0,
                'passed' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => 0,
                'duration' => 0,
            ],
            'tests' => [],
            'failures' => [],
            'errors' => [],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Find PHPUnit executable
     * 
     * @return string PHPUnit path
     */
    protected function findPhpUnit()
    {
        $paths = [
            $this->projectRoot . '/vendor/bin/phpunit',
            $this->projectRoot . '/vendor/bin/phpunit.bat',
            'phpunit',
        ];

        foreach ($paths as $path) {
            if (file_exists($path) || $this->commandExists($path)) {
                return $path;
            }
        }

        return 'phpunit'; // Fallback
    }

    /**
     * Check if command exists
     * 
     * @param string $command Command name
     * @return bool
     */
    protected function commandExists($command)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($command)));
        return !empty($return);
    }

    /**
     * Run all tests
     * 
     * @param array $options Test options
     * @return array Test results
     */
    public function runAllTests($options = [])
    {
        $this->initializeResults();
        $startTime = microtime(true);

        $testTypes = $options['types'] ?? ['unit', 'integration'];
        $coverage = $options['coverage'] ?? false;
        $parallel = $options['parallel'] ?? false;

        foreach ($testTypes as $type) {
            $this->runTestType($type, $coverage);
        }

        $this->results['summary']['duration'] = microtime(true) - $startTime;

        return $this->results;
    }

    /**
     * Run unit tests
     * 
     * @param bool $coverage Generate coverage report
     * @param bool $parallel Run in parallel
     * @return array Test results
     */
    public function runUnitTests($coverage = false, $parallel = false)
    {
        return $this->runTestType('unit', $coverage, $parallel);
    }

    /**
     * Run integration tests
     * 
     * @param bool $coverage Generate coverage report
     * @return array Test results
     */
    public function runIntegrationTests($coverage = false)
    {
        return $this->runTestType('integration', $coverage);
    }

    /**
     * Run security tests
     * 
     * @return array Test results
     */
    public function runSecurityTests()
    {
        return $this->runTestType('security');
    }

    /**
     * Run specific test type
     * 
     * @param string $type Test type
     * @param bool $coverage Generate coverage
     * @param bool $parallel Run in parallel
     * @return array Test results
     */
    protected function runTestType($type, $coverage = false, $parallel = false)
    {
        $testDir = $this->projectRoot . '/tests/' . ucfirst($type);

        if (!is_dir($testDir)) {
            return [
                'type' => $type,
                'status' => 'skipped',
                'message' => "Test directory not found: {$testDir}",
            ];
        }

        $command = $this->buildPhpUnitCommand($testDir, $coverage, $parallel);
        $output = $this->executeCommand($command);

        $result = $this->parsePhpUnitOutput($output, $type);
        $this->mergeResults($result);

        return $result;
    }

    /**
     * Build PHPUnit command
     * 
     * @param string $testDir Test directory
     * @param bool $coverage Generate coverage
     * @param bool $parallel Run in parallel
     * @return string Command
     */
    protected function buildPhpUnitCommand($testDir, $coverage = false, $parallel = false)
    {
        $command = $this->phpunitPath;
        $command .= ' ' . escapeshellarg($testDir);
        $command .= ' --colors=never';
        $command .= ' --testdox';

        if ($coverage) {
            $coverageDir = $this->projectRoot . '/storage/coverage';
            if (!is_dir($coverageDir)) {
                mkdir($coverageDir, 0755, true);
            }
            $command .= ' --coverage-html ' . escapeshellarg($coverageDir);
            $command .= ' --coverage-text';
        }

        if ($parallel) {
            $command .= ' --process-isolation';
        }

        return $command;
    }

    /**
     * Execute command
     * 
     * @param string $command Command to execute
     * @return array Output
     */
    protected function executeCommand($command)
    {
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        return [
            'output' => implode("\n", $output),
            'return_code' => $returnCode,
        ];
    }

    /**
     * Parse PHPUnit output
     * 
     * @param array|string $commandResult Command result
     * @param string $type Test type
     * @return array Parsed results
     */
    protected function parsePhpUnitOutput($commandResult, $type)
    {
        $output = $commandResult['output'];
        $result = [
            'type' => $type,
            'status' => $commandResult['return_code'] === 0 ? 'passed' : 'failed',
            'tests' => 0,
            'assertions' => 0,
            'failures' => 0,
            'errors' => 0,
            'skipped' => 0,
            'output' => $output,
        ];

        // Parse test counts
        if (preg_match('/Tests:\s+(\d+),\s+Assertions:\s+(\d+)/', $output, $matches)) {
            $result['tests'] = (int) $matches[1];
            $result['assertions'] = (int) $matches[2];
        }

        // Parse failures
        if (preg_match('/Failures:\s+(\d+)/', $output, $matches)) {
            $result['failures'] = (int) $matches[1];
        }

        // Parse errors
        if (preg_match('/Errors:\s+(\d+)/', $output, $matches)) {
            $result['errors'] = (int) $matches[1];
        }

        // Parse skipped
        if (preg_match('/Skipped:\s+(\d+)/', $output, $matches)) {
            $result['skipped'] = (int) $matches[1];
        }

        return $result;
    }

    /**
     * Merge results into main results
     * 
     * @param array $result Test result
     * @return void
     */
    protected function mergeResults($result)
    {
        $this->results['tests'][] = $result;

        if (isset($result['tests'])) {
            $this->results['summary']['total_tests'] += $result['tests'];
        }

        if (isset($result['failures'])) {
            $this->results['summary']['failed'] += $result['failures'];
        }

        if (isset($result['errors'])) {
            $this->results['summary']['errors'] += $result['errors'];
        }

        if (isset($result['skipped'])) {
            $this->results['summary']['skipped'] += $result['skipped'];
        }

        $passed = ($result['tests'] ?? 0) - ($result['failures'] ?? 0) - ($result['errors'] ?? 0) - ($result['skipped'] ?? 0);
        $this->results['summary']['passed'] += max(0, $passed);
    }

    /**
     * Run specific test file
     * 
     * @param string $testFile Test file path
     * @return array Test result
     */
    public function runTestFile($testFile)
    {
        if (!file_exists($testFile)) {
            return [
                'status' => 'error',
                'message' => "Test file not found: {$testFile}",
            ];
        }

        $command = $this->buildPhpUnitCommand($testFile);
        $output = $this->executeCommand($command);

        return $this->parsePhpUnitOutput($output, 'single');
    }

    /**
     * Run specific test method
     * 
     * @param string $testFile Test file path
     * @param string $methodName Test method name
     * @return array Test result
     */
    public function runTestMethod($testFile, $methodName)
    {
        if (!file_exists($testFile)) {
            return [
                'status' => 'error',
                'message' => "Test file not found: {$testFile}",
            ];
        }

        $command = $this->phpunitPath . ' ' . escapeshellarg($testFile) . ' --filter ' . escapeshellarg($methodName);
        $output = $this->executeCommand($command);

        return $this->parsePhpUnitOutput($output, 'method');
    }

    /**
     * Get test results
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
     * @return string HTML report
     */
    public function generateHtmlReport()
    {
        $passRate = $this->results['summary']['total_tests'] > 0
            ? round(($this->results['summary']['passed'] / $this->results['summary']['total_tests']) * 100, 2)
            : 0;

        $statusColor = $passRate >= 80 ? '#4caf50' : ($passRate >= 60 ? '#ff9800' : '#f44336');

        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Test Results Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid ' . $statusColor . '; padding-bottom: 10px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { padding: 20px; border-radius: 5px; text-align: center; background: #f8f9fa; }
        .stat-box h3 { margin: 0; font-size: 32px; }
        .stat-box p { margin: 5px 0 0 0; color: #666; }
        .passed { background: #e8f5e9; border-left: 4px solid #4caf50; }
        .failed { background: #ffebee; border-left: 4px solid #f44336; }
        .errors { background: #fff3e0; border-left: 4px solid #ff9800; }
        .pass-rate { font-size: 48px; font-weight: bold; color: ' . $statusColor . '; }
        .test-result { margin: 15px 0; padding: 15px; border-radius: 5px; background: #f8f9fa; }
        .test-result h4 { margin: 0 0 10px 0; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; color: white; }
        .status-passed { background: #4caf50; }
        .status-failed { background: #f44336; }
        .output { background: #263238; color: #aed581; padding: 15px; border-radius: 5px; overflow-x: auto; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Test Results Report</h1>
        <p><strong>Test Date:</strong> ' . $this->results['timestamp'] . '</p>
        <p><strong>Duration:</strong> ' . round($this->results['summary']['duration'], 2) . ' seconds</p>
        
        <div class="summary">
            <div class="stat-box">
                <div class="pass-rate">' . $passRate . '%</div>
                <p>Pass Rate</p>
            </div>
            <div class="stat-box">
                <h3>' . $this->results['summary']['total_tests'] . '</h3>
                <p>Total Tests</p>
            </div>
            <div class="stat-box passed">
                <h3>' . $this->results['summary']['passed'] . '</h3>
                <p>Passed</p>
            </div>
            <div class="stat-box failed">
                <h3>' . $this->results['summary']['failed'] . '</h3>
                <p>Failed</p>
            </div>
            <div class="stat-box errors">
                <h3>' . $this->results['summary']['errors'] . '</h3>
                <p>Errors</p>
            </div>
        </div>

        <h2>Test Results by Type</h2>';

        foreach ($this->results['tests'] as $test) {
            $statusClass = $test['status'] === 'passed' ? 'passed' : 'failed';
            $statusBadge = $test['status'] === 'passed' ? 'status-passed' : 'status-failed';

            $html .= '
            <div class="test-result">
                <h4>
                    <span class="status-badge ' . $statusBadge . '">' . strtoupper($test['status']) . '</span>
                    ' . ucfirst($test['type']) . ' Tests
                </h4>
                <p><strong>Tests:</strong> ' . ($test['tests'] ?? 0) . ' | 
                   <strong>Failures:</strong> ' . ($test['failures'] ?? 0) . ' | 
                   <strong>Errors:</strong> ' . ($test['errors'] ?? 0) . ' | 
                   <strong>Skipped:</strong> ' . ($test['skipped'] ?? 0) . '</p>';

            if (!empty($test['output'])) {
                $html .= '<details><summary>View Output</summary><div class="output">' .
                    htmlspecialchars($test['output']) . '</div></details>';
            }

            $html .= '</div>';
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Save HTML report
     * 
     * @param string $filePath Report file path
     * @return bool Success
     */
    public function saveHtmlReport($filePath = null)
    {
        $filePath = $filePath ?? $this->projectRoot . '/storage/reports/test-report.html';

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $html = $this->generateHtmlReport();
        return file_put_contents($filePath, $html) !== false;
    }
}
