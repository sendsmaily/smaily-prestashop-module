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

class SmailyForPrestaShopSmailyRssFeedModuleFrontController extends ModuleFrontController
{
    public const ALLOWED_SORT_BY_VALUES = ['date_add', 'date_upd', 'name', 'price', 'id_product'];

    public function initContent()
    {
        parent::initContent();

        $limit = (int) Tools::getValue('limit');
        $limit = $limit >= 1 && $limit <= 250 ? $limit : 50;

        $sortBy = Tools::getValue('sort_by');
        $sortBy = in_array($sortBy, $this::ALLOWED_SORT_BY_VALUES, true) ? $sortBy : 'date_upd';

        $sortOrder = Tools::getValue('sort_order');
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'desc';

        $categoryId = (int) Tools::getValue('category_id');
        $categoryId = $categoryId <= 0 ? false : $categoryId;

        header('Content-Type: application/xml');
        echo $this->generateRssFeed($categoryId, $limit, $sortBy, $sortOrder);
        exit; // Stop to render XML instead of twig template.
    }

    public function generateRssFeed($categoryId, $limit, $sortBy, $sortOrder): string
    {
        /** @var \PrestaShop\Module\SmailyForPrestaShop\Repository\RssFeedProductsRepository $repository */
        $repository = $this->get('prestashop.module.smailyforprestashop.front.rss_feed_product_repository');

        $baseUrl = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $rss = '<?xml version="1.0" encoding="utf-8"?>' .
            '<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">' .
            '<channel><title>Store</title><link>' .
            htmlspecialchars($baseUrl) . '</link><description>Product Feed</description><lastBuildDate>' .
            date('D, d M Y H:i:s') . '</lastBuildDate>';

        $products = $repository->getProducts($categoryId, $limit, $sortBy, $sortOrder);
        foreach ($products as $product) {
            // Product data by id.
            $prod = new Product($product['id_product']);
            // Product name.
            $name = $product['name'];
            // Product url.
            $product_url = $prod->getLink();
            // Date added.
            $date_add = $prod->date_add;
            // Description (short).
            $description_short = $product['description_short'];
            // Product image.
            $image = $prod->getCover($product['id_product']);
            $image = new Image($image['id_image']);
            $product_photo = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.' .
                $image->image_format;
            // Product price with tax.
            $price = $prod->getPrice();
            // Get product price without discount.
            $full_price = $prod->getPriceWithoutReduct();
            // Determine if there is discount.
            $discount = 0;
            if ($full_price > $price && $price > 0) {
                $discount = ceil(($full_price - $price) / $full_price * 100);
            }
            // Add currency symbol.
            $currencySymbol = Currency::getDefaultCurrency()->sign;
            $price = number_format($price, 2, '.', ',') . $currencySymbol;
            $full_price = number_format($full_price, 2, '.', ',') . $currencySymbol;
            $price_fields = '';
            if ($discount > 0) {
                $price_fields = '<smly:old_price>' . $full_price . '</smly:old_price><smly:discount>-' .
                                $discount . '%</smly:discount>';
            }
            $rss .= '<item>
            <title><![CDATA[' . $name . ']]></title>

            <link><![CDATA[' . $product_url . ']]></link>
            <guid isPermaLink="True">' . $baseUrl . '</guid>
            <pubDate>' . date('D, d M Y H:i:s', strtotime($date_add)) . '</pubDate>
            <description><![CDATA[' . $description_short . ']]></description>
            <enclosure url="' . $product_photo . '" />
            <smly:price>' . $price . '</smly:price>' . $price_fields . '
            </item>';
        }
        $rss .= '</channel></rss>';

        return $rss;
    }
}
