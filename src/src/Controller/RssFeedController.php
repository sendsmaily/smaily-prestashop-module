<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

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
        $rss = '<?xml version="1.0" encoding="utf-8"?>' .
            '<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">' .
            '<channel><title>Store</title><link>' .
            htmlspecialchars($baseUrl) . '</link><description>Product Feed</description><lastBuildDate>' .
            date('D, d M Y H:i:s') . '</lastBuildDate>';

        $products = $this->collection->getProducts($categoryId, $limit, $sortBy, $sortOrder);
        foreach ($products as $product) {
            // Product data by id.
            $prod = new \Product($product['id_product']);
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
            $image = new \Image($image['id_image']);
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
            $currencySymbol = \Currency::getDefaultCurrency()->sign;
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
