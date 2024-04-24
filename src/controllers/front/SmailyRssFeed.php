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

class SmailyForPrestaShopSmailyRssFeedModuleFrontController extends ModuleFrontController
{
    public const ALLOWED_SORT_BY_VALUES = ['date_add', 'date_upd', 'name', 'price', 'id_product'];

    public function initContent()
    {
        parent::initContent();
        $baseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $limit = (int) Tools::getValue('limit');
        $limit = $limit >= 1 && $limit <= 250 ? $limit : 50;

        $sortBy = Tools::getValue('sort_by');
        $sortBy = in_array($sortBy, $this::ALLOWED_SORT_BY_VALUES, true) ? $sortBy : 'date_upd';

        $sortOrder = Tools::getValue('sort_order');
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'desc';

        $categoryId = (int) Tools::getValue('category_id');
        $categoryId = $categoryId <= 0 ? false : $categoryId;

        $controller = $this->get('prestashop.module.smailyforprestashop.controller.rss_feed_controller');

        header('Content-Type: application/xml');
        echo $controller->generateFeed($baseUrl, $categoryId, $limit, $sortBy, $sortOrder);

        exit; // Stop to render XML instead of twig template.
    }
}
