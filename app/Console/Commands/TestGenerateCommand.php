<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Testing\OxygenTestGenerator;

/**
 * TestGenerateCommand - Auto-Generate Tests
 * 
 * Usage: php oxygen test:generate [--type=unit|integration|all] [--ai]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class TestGenerateCommand extends Command
{
    protected $name = 'test:generate';
    protected $description = 'Auto-generate tests for all components';

    public function execute($args = [])
    {
        $this->info("🧪 Generating Tests...\n");

        $type = $this->getOption($args, '--type', 'all');
        $useAI = $this->hasOption($args, '--ai');

        $generator = new OxygenTestGenerator($useAI);

        $this->info("Test Type: {$type}");
        $this->info("AI-Powered: " . ($useAI ? 'Yes' : 'No') . "\n");

        $generatedTests = $generator->generateAllTests($type);

        $this->displayResults($generatedTests);

        $this->success("\n✓ Test generation completed!");
        $this->info("Run tests with: php oxygen test:all");
    }

    protected function displayResults($tests)
    {
        $this->info("\n" . str_repeat("=", 60));
        $this->info("GENERATED TESTS");
        $this->info(str_repeat("=", 60) . "\n");

        $this->info("Total Files Generated: " . count($tests) . "\n");

        foreach ($tests as $index => $testFile) {
            $num = $index + 1;
            $relativePath = str_replace(getcwd() . '/', '', $testFile);
            echo "{$num}. {$relativePath}\n";
        }
    }

    protected function getOption($args, $option, $default = null)
    {
        foreach ($args as $arg) {
            if (strpos($arg, $option . '=') === 0) {
                return substr($arg, strlen($option) + 1);
            }
        }
        return $default;
    }

    protected function hasOption($args, $option)
    {
        return in_array($option, $args);
    }
}
