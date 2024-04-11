<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Lib;

class Logger
{
    /**
     * Add information to PrestaShop log.
     *
     * @param string $message
     * @param int $severity (1 is informative, 3 error)
     *
     * @return void
     */
    public static function logMessageWithSeverity($message, $severity)
    {
        \PrestaShopLogger::addLog('[SMAILY] ' . $message, $severity);
    }

    /**
     * Add error (severity 3) to PrestaShop log with formatted arguments.
     *
     * @param string $message
     *
     * @return void
     */
    public static function logErrorWithFormatting()
    {
        $args = func_get_args();
        $message = call_user_func_array('sprintf', $args);
        \PrestaShopLogger::addLog('[SMAILY] ' . $message, 3);
    }
}
