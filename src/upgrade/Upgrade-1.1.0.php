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
 * Upgrades database with smaily abandoned cart table.
 */
function upgrade_module_1_1_0()
{
    return Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'smaily_cart (
            `id_smaily_cart` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `id_customer` INT UNSIGNED NULL ,
            `id_cart` INT UNSIGNED NULL ,
            `date_sent` DATETIME NOT NULL) ENGINE=' . _MYSQL_ENGINE_
    );
}
