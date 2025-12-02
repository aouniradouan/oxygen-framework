<?php

namespace Oxygen\Console;

/**
 * Command - Base class for all console commands
 * 
 * All console commands should extend this class and implement the execute() method.
 * 
 * @package    Oxygen\Console
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Command
{
    /**
     * Execute the command
     * 
     * @param array $arguments Command arguments
     * @return void
     */
    abstract public function execute($arguments);

    /**
     * Check if an option is present in arguments
     * 
     * @param array $args Arguments array
     * @param string $option Option to check
     * @return bool
     */
    protected function hasOption($args, $option)
    {
        return in_array($option, $args);
    }

    /**
     * Display a success message
     * 
     * @param string $message Success message
     * @return void
     */
    public function success($message)
    {
        echo "\033[32m✓ {$message}\033[0m\n";
    }

    /**
     * Display an error message
     * 
     * @param string $message Error message
     * @return void
     */
    public function error($message)
    {
        echo "\033[31m✗ {$message}\033[0m\n";
    }

    /**
     * Display an info message
     * 
     * @param string $message Info message
     * @return void
     */
    public function info($message)
    {
        echo "\033[36mℹ {$message}\033[0m\n";
    }

    /**
     * Display a warning message
     * 
     * @param string $message Warning message
     * @return void
     */
    public function warning($message)
    {
        echo "\033[33m⚠ {$message}\033[0m\n";
    }

    /**
     * Create a file with content
     * 
     * @param string $path File path
     * @param string $content File content
     * @return bool
     */
    protected function createFile($path, $content)
    {
        // Create directory if it doesn't exist
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Check if file already exists
        if (file_exists($path)) {
            $this->error("File already exists: {$path}");
            return false;
        }

        // Write file
        file_put_contents($path, $content);
        return true;
    }
    /**
     * Display a line of text
     * 
     * @param string $message Message to display
     * @return void
     */
    public function line($message)
    {
        echo "{$message}\n";
    }

    /**
     * Ask user for input
     * 
     * @param string $question Question to ask
     * @param string $default Default value
     * @return string
     */
    public function ask($question, $default = null)
    {
        $defaultText = $default ? " [{$default}]" : "";
        echo "\033[36m?\033[0m {$question}{$defaultText}: ";
        $input = trim(fgets(STDIN));
        return empty($input) && $default ? $default : $input;
    }

    /**
     * Ask for confirmation
     * 
     * @param string $question Question to ask
     * @param bool $default Default value
     * @return bool
     */
    public function confirm($question, $default = true)
    {
        $defaultText = $default ? " [Y/n]" : " [y/N]";
        echo "\033[36m?\033[0m {$question}{$defaultText}: ";
        $input = trim(fgets(STDIN));

        if (empty($input)) {
            return $default;
        }

        return in_array(strtolower($input), ['y', 'yes', 'true', '1']);
    }

    /**
     * Ask a choice question
     * 
     * @param string $question Question to ask
     * @param array $choices Available choices
     * @param mixed $default Default value
     * @return mixed
     */
    public function choice($question, array $choices, $default = null)
    {
        $this->info($question);

        foreach ($choices as $index => $choice) {
            $this->line("  [" . ($index + 1) . "] " . $choice);
        }

        $defaultIndex = null;
        if ($default) {
            $key = array_search($default, $choices);
            if ($key !== false) {
                $defaultIndex = $key + 1;
            }
        }

        $defaultText = $defaultIndex ? " [{$defaultIndex}]" : "";
        echo "\033[36m?\033[0m Select option{$defaultText}: ";

        $input = trim(fgets(STDIN));

        if (empty($input) && $defaultIndex) {
            return $default;
        }

        $index = (int) $input - 1;

        if (isset($choices[$index])) {
            return $choices[$index];
        }

        $this->error("Invalid selection.");
        return $this->choice($question, $choices, $default);
    }
}
