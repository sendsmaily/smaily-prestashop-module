<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
            '<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">'.
            '<channel><title>Store</title><link>'.
            htmlspecialchars($baseUrl).'</link><description>Product Feed</description><lastBuildDate>'.
            date("D, d M Y H:i:s").'</lastBuildDate>';
        foreach ($products as $product) {
            $link = new Link();
            $product_url = $link->getProductLink((int)$product['id_product']);
            $image = Product::getCover((int)$product['id_product']);
            $image = new Image($image['id_image']);
            $product_photo = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
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
                $price_fields = '<smly:old_price>'.$price.'</smly:old_price><smly:discount>-' .
                                $discount.'%</smly:discount>';
            }
            $rss .= '<item>
            <title>'.htmlspecialchars($name).'</title>
            
            <link>'.htmlspecialchars($product_url).'</link>
            <guid isPermaLink="True">'.htmlspecialchars($baseUrl).'</guid>
            <pubDate>'.date("D, d M Y H:i:s", htmlspecialchars(strtotime($date_add))).'</pubDate>
            <description>'.htmlspecialchars(strip_tags($product['description_short'])).'</description>
            <enclosure url="'.htmlspecialchars($product_photo).'" />
            <smly:price>'.htmlspecialchars($splcPrice).'</smly:price>'.htmlspecialchars($price_fields).'
            </item>';
        }
        $rss .='</channel></rss>';
        header('Content-Type: application/xml');
        echo $rss;
        die;
    }
}
