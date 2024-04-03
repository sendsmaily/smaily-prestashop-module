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
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Migrates existing abandoned cart fields to new structure.
 */
function upgrade_module_1_3_0()
{
    $sync_fields = unserialize(Configuration::get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'));

    // Replace description_short->description.
    $description_short = array_search('description_short', $sync_fields);
    if ($description_short !== false) {
        unset($sync_fields[$description_short]);
        if (!in_array('description', $sync_fields)) {
            array_push($sync_fields, 'description');
        }
    }

    $cartEnabled = Configuration::get('SMAILY_ENABLE_ABANDONED_CART') === '1' ? true : false;
    // Add the previous default fields to sync array.
    if ($cartEnabled) {
        array_push($sync_fields, 'first_name', 'last_name');
    }

    // Remove product category field.
    $category = array_search('category', $sync_fields);
    if ($category !== false) {
        unset($sync_fields[$category]);
    }

    return Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize($sync_fields));
}
