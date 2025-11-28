<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Security\OxygenVirusScanner;

/**
 * VirusScanCommand - Malware Detection
 * 
 * Usage: php oxygen virus:scan [--quarantine] [--deep]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class VirusScanCommand extends Command
{
    protected $name = 'virus:scan';
    protected $description = 'Scan project for viruses and malware';

    public function execute($args = [])
    {
        $this->info("🦠 Starting Virus Scan...\n");

        $deepScan = $this->hasOption($args, '--deep');
        $quarantine = $this->hasOption($args, '--quarantine');

        $force = $this->hasOption($args, '--force');

        $scanner = new OxygenVirusScanner();
        $projectPath = getcwd();

        $this->info("Scanning: {$projectPath}");
        $this->info("Deep Scan: " . ($deepScan ? 'Yes' : 'No'));
        $this->info("Auto-Quarantine: " . ($quarantine ? 'Yes' : 'No') . "\n");

        if ($quarantine && !$force) {
            $this->warning("⚠️  You have enabled auto-quarantine.");
            if (!$this->confirm("Are you sure you want to automatically move infected files to quarantine?", false)) {
                $this->info("Quarantine disabled for this run.");
                $quarantine = false;
            }
        }

        $results = $scanner->scanProject($projectPath, $deepScan, $quarantine);

        $this->displayResults($results);
        $this->generateReport($scanner);

        if ($results['summary']['infected_files'] > 0) {
            exit(1);
        }
    }

    protected function displayResults($results)
    {
        $summary = $results['summary'];

        $this->info("\n" . str_repeat("=", 60));
        $this->info("VIRUS SCAN RESULTS");
        $this->info(str_repeat("=", 60) . "\n");

        $this->info("Files Scanned: {$summary['scanned_files']}");
        $this->error("Infected Files: {$summary['infected_files']}");

        if ($summary['quarantined_files'] > 0) {
            $this->warning("Quarantined Files: {$summary['quarantined_files']}");
        }

        if ($summary['infected_files'] === 0) {
            $this->success("\n✓ No threats detected! Your system is clean.");
            return;
        }

        $this->info("\n" . str_repeat("-", 60));
        $this->info("THREATS FOUND");
        $this->info(str_repeat("-", 60) . "\n");

        foreach ($results['threats'] as $index => $threat) {
            $num = $index + 1;
            echo "\n{$num}. ";
            $this->error($threat['file']);
            echo "   Threats: {$threat['threat_count']}\n";
            echo "   Hash: {$threat['file_hash']}\n";

            foreach ($threat['threats'] as $t) {
                echo "   - [{$t['severity']}] {$t['type']}: {$t['description']}\n";
            }
        }
    }

    protected function generateReport($scanner)
    {
        $reportDir = getcwd() . '/storage/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $reportFile = $reportDir . '/virus-scan-' . date('Y-m-d_H-i-s') . '.html';
        file_put_contents($reportFile, $scanner->generateHtmlReport());

        $this->info("\n📄 Report: {$reportFile}");
    }
}
