<?php

namespace Oxygen\Core\Log;

/**
 * Logger Class
 * 
 * Provides logging functionality with multiple channels and log levels.
 * 
 * @package    Oxygen\Core\Log
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class Logger
{
    /**
     * Log levels
     */
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * Log channels
     * 
     * @var array
     */
    protected $channels = [];

    /**
     * Default channel
     * 
     * @var string
     */
    protected $defaultChannel = 'default';

    /**
     * Log directory
     * 
     * @var string
     */
    protected $logPath;

    /**
     * Create a new logger instance
     * 
     * @param string $logPath
     */
    public function __construct($logPath = null)
    {
        $this->logPath = $logPath ?: __DIR__ . '/../../../storage/logs';
        
        // Ensure log directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log an emergency message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function emergency($message, array $context = [], $channel = null)
    {
        $this->log(self::EMERGENCY, $message, $context, $channel);
    }

    /**
     * Log an alert message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function alert($message, array $context = [], $channel = null)
    {
        $this->log(self::ALERT, $message, $context, $channel);
    }

    /**
     * Log a critical message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function critical($message, array $context = [], $channel = null)
    {
        $this->log(self::CRITICAL, $message, $context, $channel);
    }

    /**
     * Log an error message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function error($message, array $context = [], $channel = null)
    {
        $this->log(self::ERROR, $message, $context, $channel);
    }

    /**
     * Log a warning message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function warning($message, array $context = [], $channel = null)
    {
        $this->log(self::WARNING, $message, $context, $channel);
    }

    /**
     * Log a notice message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function notice($message, array $context = [], $channel = null)
    {
        $this->log(self::NOTICE, $message, $context, $channel);
    }

    /**
     * Log an info message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function info($message, array $context = [], $channel = null)
    {
        $this->log(self::INFO, $message, $context, $channel);
    }

    /**
     * Log a debug message
     * 
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function debug($message, array $context = [], $channel = null)
    {
        $this->log(self::DEBUG, $message, $context, $channel);
    }

    /**
     * Log a message
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function log($level, $message, array $context = [], $channel = null)
    {
        $channel = $channel ?: $this->defaultChannel;
        $logFile = $this->logPath . '/' . $channel . '-' . date('Y-m-d') . '.log';

        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $contextString
        );

        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get a channel-specific logger
     * 
     * @param string $channel
     * @return self
     */
    public function channel($channel)
    {
        $logger = clone $this;
        $logger->defaultChannel = $channel;
        return $logger;
    }
}

