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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AbandonedCartCollection
{
    /**
     * @var \Db;
     */
    private $db;

    /**
     * @var AbandonedCart[]
     */
    public $carts;

    public function __construct()
    {
        $this->db = \Db::getInstance();
        $this->carts = $this->getCarts();
    }

    /**
     * Gets abandoned cart data from DB.
     *
     * @return AbandonedCart[] Abandoned carts array
     */
    private function getCarts()
    {
        // Legacy method that requires trimming as query strings were previously concatenated.
        $shopRestriction = ltrim(\Shop::addSqlRestriction(\Shop::SHARE_CUSTOMER, 'c'), ' AND ');

        $sql = new \DbQuery();
        $sql->select('c.`id_cart`, c.`id_customer`, c.`date_upd`, cu.`firstname`, cu.`lastname`, cu.`email`');
        $sql->from('cart', 'c');
        $sql->leftJoin('orders', 'o', 'o.`id_cart` = c.`id_cart`');
        $sql->rightJoin('customer', 'cu', 'cu.`id_customer` = c.`id_customer`');
        $sql->leftOuterJoin('smaily_cart', 'sc', 'c.`id_cart` = sc.`id_cart`');
        $sql->where('sc.`id_cart` IS NULL');
        $sql->where('DATE_SUB(CURDATE(),INTERVAL 10 DAY) <= c.date_add');
        $sql->where('o.`id_order` IS NULL');
        $sql->where($shopRestriction);
        $sql->groupBy('cu.`id_customer`');

        $carts = $this->db->executeS($sql);

        $result = [];
        foreach ($carts as $abandonedCart) {
            $prestaCart = new \Cart($abandonedCart['id_cart']);
            $products = $prestaCart->getProducts();
            // Don't continue if no products in cart.
            if (empty($products)) {
                continue;
            }

            $cart = new AbandonedCart();
            $cart->cartID = (int) $abandonedCart['id_cart'];
            $cart->customerID = (int) $abandonedCart['id_customer'];
            $cart->dateUpdated = $abandonedCart['date_upd'];
            $cart->email = $abandonedCart['email'];
            $cart->firstName = $abandonedCart['firstname'];
            $cart->lastName = $abandonedCart['lastname'];

            foreach ($products as $product) {
                $abandonedCartProduct = new AbandonedCartProduct();
                $abandonedCartProduct->name = $product['name'];
                $abandonedCartProduct->description = $product['description_short'];
                $abandonedCartProduct->sku = $product['reference'];
                $abandonedCartProduct->price = $product['price_with_reduction'];
                $abandonedCartProduct->basePrice = $product['price_without_reduction'];
                $abandonedCartProduct->quantity = $product['quantity'];

                $cart->products[] = $abandonedCartProduct;
            }

            $result[] = $cart;
        }

        return $result;
    }
}
