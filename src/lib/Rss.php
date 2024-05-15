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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Rss
{
    /**
     * Make RSS URL with query parameters.
     *
     * @return string
     */
    public static function buildRssUrl(string $categoryId, string $limit, string $sortBy, string $sortOrder): string
    {
        $query_arguments = [
            'limit' => $limit,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];

        if (!empty($categoryId)) {
            $query_arguments['category_id'] = $categoryId;
        }

        return \Context::getContext()->link->getModuleLink(
            'smailyforprestashop',
            'SmailyRssFeed',
            $query_arguments
        );
    }
}
