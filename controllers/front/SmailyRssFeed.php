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

class SmailyforprestashopSmailyRssFeedModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->generateRssFeed();
    }

    public function generateRssFeed()
    {
        $products = Product::getProducts($this->context->language->id, 0, 50, 'date_upd', 'desc', false, true);
        $baseUrl = Tools::getHttpHost(true).__PS_BASE_URI__;
        $rss ='<?xml version="1.0" encoding="utf-8"?>' .
            '<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">' .
            '<channel><title>Store</title><link>' .
            htmlspecialchars($baseUrl).'</link><description>Product Feed</description><lastBuildDate>' .
            date("D, d M Y H:i:s") . '</lastBuildDate>';
        foreach ($products as $product) {
            $link = new Link();
            $product_url = $link->getProductLink((int)$product['id_product']);
            $image = Product::getCover((int)$product['id_product']);
            $image = new Image($image['id_image']);
            $product_photo = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";
            $price = $product['price'];
            $splcPrice = $product['wholesale_price'];
            $date_add = $product['date_add'];
            $name = $product['name'];
            $discount = 0;
            if ($splcPrice  == 0) {
                $splcPrice = $price;
            }
            if ($splcPrice < $price && $price > 0) {
                $discount = ceil(($price-$splcPrice)/$price*100);
            }
            $currencysymbol = Currency::getDefaultCurrency()->sign;
            $price = number_format($price, 2, '.', ',') . $currencysymbol;
            $splcPrice = number_format($splcPrice, 2, '.', ',') . $currencysymbol;
            $price_fields ='';
            if ($discount > 0) {
                $price_fields = '<smly:old_price>' . $price . '</smly:old_price><smly:discount>-' .
                                $discount . '%</smly:discount>';
            }
            $rss .= '<item>
            <title>'.htmlspecialchars($name).'</title>
            
            <link>'.htmlspecialchars($product_url) . '</link>
            <guid isPermaLink="True">'.htmlspecialchars($baseUrl) . '</guid>
            <pubDate>' . date("D, d M Y H:i:s", htmlspecialchars(strtotime($date_add))) . '</pubDate>
            <description>' . htmlspecialchars(strip_tags($product['description_short'])) . '</description>
            <enclosure url="' . htmlspecialchars($product_photo) . '" />
            <smly:price>' . htmlspecialchars($splcPrice) . '</smly:price>' . htmlspecialchars($price_fields) . '
            </item>';
        }
        $rss .='</channel></rss>';
        header('Content-Type: application/xml');
        echo $rss;
        die;
    }
}
