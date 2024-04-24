<?php
/**
 * 2024 Smaily
 *
 * NOTICE OF LICENSE
 *
 * Smaily for PrestaShop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for PrestaShop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for PrestaShop. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Smaily <info@smaily.com>
 * @copyright 2024 Smaily
 * @license   GPL3
 */
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
