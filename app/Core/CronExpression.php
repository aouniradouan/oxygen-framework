<?php

namespace Oxygen\Core;

/**
 * CronExpression - Cron Expression Parser
 * 
 * Parses cron expressions and determines if they are due.
 * Supports standard cron format: * * * * * (min hour day month week)
 * 
 * @package    Oxygen\Core
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    1.0.0
 */
class CronExpression
{
    /**
     * Determine if the cron expression is due
     * 
     * @param string $expression Cron expression
     * @return bool
     */
    public static function isDue($expression)
    {
        $date = date('i G j n w'); // min hour day month week
        $current = explode(' ', $date);
        $cron = explode(' ', $expression);

        if (count($cron) !== 5) {
            return false;
        }

        foreach ($cron as $index => $segment) {
            if ($segment === '*') {
                continue;
            }

            if (strpos($segment, ',') !== false) {
                $parts = explode(',', $segment);
                if (!in_array($current[$index], $parts)) {
                    return false;
                }
                continue;
            }

            if (strpos($segment, '/') !== false) {
                $parts = explode('/', $segment);
                if ($current[$index] % $parts[1] !== 0) {
                    return false;
                }
                continue;
            }

            if ($segment != $current[$index]) {
                return false;
            }
        }

        return true;
    }
}
