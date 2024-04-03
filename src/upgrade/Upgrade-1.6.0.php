<?php
/**
 * 2021 Smaily
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
 * @copyright 2021 Smaily
 * @license   GPL3
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_0()
{
    $customer_sync_fields = unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'));

    // Update firstname and lastname fields names.
    $fields = [];
    foreach ($customer_sync_fields as $field) {
        if ($field === 'firstname') {
            $fields[] = 'first_name';
        } elseif ($field === 'lastname') {
            $fields[] = 'last_name';
        } else {
            $fields[] = $field;
        }
    }

    $customer_sync_fields = $fields;

    return Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize($customer_sync_fields));
}
