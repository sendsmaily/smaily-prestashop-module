<?php
/**
 * 2018 Smaily
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
 * @copyright 2018 Smaily
 * @license   GPL3
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Migrates existing abandoned cart fields to new structure.
 */
function upgrade_module_1_5_0($object)
{
    Configuration::updateValue('SMAILY_ENABLE_OPTIN_IF_CUSTOMER_JOINS_WITH_SUBSCRIPTION', 0);
    Configuration::updateValue('SMAILY_CUSTOMER_JOIN_AUTORESPONDER', '');
    return $this->registerHook('actionCustomerAccountAdd')
}
