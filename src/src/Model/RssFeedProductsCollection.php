<?php

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
