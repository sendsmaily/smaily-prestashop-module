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

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

class RssFeedProductsCollection
{
    public function getProducts(false|int $categoryId, int $limit, string $sort_by, string $sort_order): array
    {
        $products = [];

        // TODO: Use service from adapter or core when available.
        $products = \Product::getProducts(
            \Context::getContext()->language->id,
            0, // start number
            $limit,
            $sort_by,
            $sort_order,
            $categoryId,
            true // only active products
        );

        return $products;
    }
}
