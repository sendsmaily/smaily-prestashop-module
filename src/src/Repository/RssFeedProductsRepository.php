<?php

namespace PrestaShop\Module\SmailyForPrestaShop\Repository;

class RssFeedProductsRepository
{
    public function getProducts(false|int $categoryId, int $limit, string $sort_by, string $sort_order): array
    {
        $products = [];

        // TODO: Use service from adapter or core
        $products = \Product::getProducts(
            \Context::getContext()->language->id,
            0, // start number
            $limit, // hardcoded 50 in < 1.4.0
            $sort_by, // hardcoded date_upd in < 1.4.0
            $sort_order, // hardcoded desc in < 1.4.0
            $categoryId, // hardcoded false in < 1.4.0
            true // only active products
        );

        return $products;
    }
}
