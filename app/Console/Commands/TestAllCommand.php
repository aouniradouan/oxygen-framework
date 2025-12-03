<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Testing\OxygenTestRunner;

/**
 * TestAllCommand - Run All Tests
 * 
 * Usage: php oxygen test:all [--coverage] [--report]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class TestAllCommand extends Command
{
    protected $name = 'test:all';
    protected $description = 'Run all tests (unit, integration, security)';

    public function execute($args = [])
    {
        $this->info("🧪 Running All Tests...\n");

        $coverage = $this->hasOption($args, '--coverage');
        $report = $this->hasOption($args, '--report');

        $runner = new OxygenTestRunner();

        $options = [
            'types' => ['unit', 'integration', 'security'],
            'coverage' => $coverage,
            'parallel' => false,
        ];

        $results = $runner->runAllTests($options);

        $this->displayResults($results);

        if ($report) {
            $reportFile = getcwd() . '/storage/reports/test-report.html';
            $runner->saveHtmlReport($reportFile);
            $this->info("\n📄 HTML Report: {$reportFile}");
        }

        if ($coverage) {
            $coverageDir = getcwd() . '/storage/coverage';
            $this->info("\n📊 Coverage Report: {$coverageDir}/index.html");
        }

        // Exit code based on results
        if ($results['summary']['failed'] > 0 || $results['summary']['errors'] > 0) {
            exit(1);
        }
    }

    protected function displayResults($results)
    {
        $summary = $results['summary'];

        $this->info("\n" . str_repeat("=", 60));
        $this->info("TEST RESULTS");
        $this->info(str_repeat("=", 60) . "\n");

        $passRate = $summary['total_tests'] > 0
            ? round(($summary['passed'] / $summary['total_tests']) * 100, 2)
            : 0;

        $this->info("Total Tests: {$summary['total_tests']}");
        $this->success("Passed: {$summary['passed']}");

        if ($summary['failed'] > 0) {
            $this->error("Failed: {$summary['failed']}");
        }

        if ($summary['errors'] > 0) {
            $this->error("Errors: {$summary['errors']}");
        }

        if ($summary['skipped'] > 0) {
            $this->warning("Skipped: {$summary['skipped']}");
        }

        $this->info("Duration: " . round($summary['duration'], 2) . "s");

        echo "\nPass Rate: ";
        if ($passRate >= 80) {
            $this->success("{$passRate}%");
        } elseif ($passRate >= 60) {
            $this->warning("{$passRate}%");
        } else {
            $this->error("{$passRate}%");
        }

        // Display individual test results
        $this->info("\n" . str_repeat("-", 60));
        $this->info("RESULTS BY TYPE");
        $this->info(str_repeat("-", 60) . "\n");

        foreach ($results['tests'] as $test) {
            $status = $test['status'] === 'passed' ? '✓' : '✗';
            $statusColor = $test['status'] === 'passed' ? 'success' : 'error';

            echo "{$status} " . ucfirst($test['type']) . " Tests: ";
            echo "{$test['tests']} tests, {$test['failures']} failures, {$test['errors']} errors\n";
        }
    }

    protected function hasOption($args, $option)
    {
        return in_array($option, $args);
    }
}
