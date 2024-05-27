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

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\SmailyForPrestaShop\Lib\Rss;
use PrestaShop\Module\SmailyForPrestaShop\Model\RssFeedProductsCollection;

class RssFeedController
{
    /**
     * @var RssFeedProductsCollection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new RssFeedProductsCollection();
    }

    public function generateFeed(string $baseUrl, mixed $categoryId, int $limit, string $sortBy, string $sortOrder): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $rss = $xml->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:smly', 'https://sendsmaily.net/schema/editor/rss.xsd');
        $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $xml->appendChild($rss);

        $channel = $xml->createElement('channel');

        $atomLink = $xml->createElement('atom:link');
        $atomLink->setAttribute('href', Rss::buildRssUrl($categoryId ? $categoryId : '', strval($limit), $sortBy, $sortOrder));
        $atomLink->setAttribute('rel', 'self');
        $atomLink->setAttribute('type', 'application/rss+xml');
        $channel->appendChild($atomLink);

        $title = $xml->createElement('title');
        $title->appendChild($xml->createCDATASection('Store'));
        $channel->appendChild($title);

        $link = $xml->createElement('link');
        $link->appendChild($xml->createCDATASection(htmlspecialchars($baseUrl)));
        $channel->appendChild($link);

        $description = $xml->createElement('description');
        $description->appendChild($xml->createCDATASection('Product Feed'));
        $channel->appendChild($description);

        $lastBuildDate = $xml->createElement('lastBuildDate');
        $lastBuildDate->appendChild($xml->createCDATASection(gmdate(DATE_RFC2822, strtotime(date('D, d M Y H:i:s')))));
        $channel->appendChild($lastBuildDate);

        $rss->appendChild($channel);

        $products = $this->collection->getProducts($categoryId, $limit, $sortBy, $sortOrder);
        foreach ($products as $product) {
            $channel->appendChild($this->generateItemNode($xml, $product));
        }

        return $xml->saveXML();
    }

    private function generateItemNode(\DOMDocument $xml, array $product): \DOMNode
    {
        $prod = new \Product($product['id_product']);

        $item = $xml->createElement('item');

        $title = $xml->createElement('title');
        $title->appendChild($xml->createCDATASection($product['name']));
        $item->appendChild($title);

        $link = $xml->createElement('link');
        $link->appendChild($xml->createCDATASection($prod->getLink()));
        $item->appendChild($link);

        $gUID = $xml->createElement('guid');
        $gUID->setAttribute('isPermaLink', 'true');
        $gUID->appendChild($xml->createCDATASection($prod->getLink()));
        $item->appendChild($gUID);

        $pubDate = $xml->createElement('pubDate');
        $pubDate->appendChild($xml->createCDATASection(gmdate(DATE_RFC2822, strtotime(date('D, d M Y H:i:s', strtotime($prod->date_add))))));
        $item->appendChild($pubDate);

        $description = $xml->createElement('description');
        $description->appendChild($xml->createCDATASection($product['description_short']));
        $item->appendChild($description);

        $image = $prod->getCover($product['id_product']);
        $image = new \Image($image['id_image']);
        $product_photo = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '.' . $image->image_format;

        $enclosure = $xml->createElement('enclosure');
        $enclosure->setAttribute('url', $product_photo);
        $enclosure->setAttribute('length', strval(filesize(sprintf('%s.%s', $image->getPathForCreation(), $image->image_format))));
        $enclosure->setAttribute('type', 'image/' . $image->image_format);
        $item->appendChild($enclosure);

        $price = $prod->getPrice();
        $full_price = $prod->getPriceWithoutReduct();
        $discountPercentage = 0;
        if ($full_price > $price && $price > 0) {
            $discountPercentage = ceil(($full_price - $price) / $full_price * 100);
        }

        $smlyPrice = $xml->createElement('smly:price');
        $smlyPrice->appendChild($xml->createCDATASection(number_format($price, 2, '.', ',') . \Currency::getDefaultCurrency()->sign));
        $item->appendChild($smlyPrice);

        if ($discountPercentage > 0) {
            $oldPrice = $xml->createElement('smly:old_price');
            $oldPrice->appendChild($xml->createCDATASection(strval($full_price)));
            $item->appendChild($oldPrice);

            $discount = $xml->createElement('smly:discount');
            $discount->appendChild($xml->createCDATASection($discountPercentage . '%'));
            $item->appendChild($discount);
        }

        return $item;
    }
}
