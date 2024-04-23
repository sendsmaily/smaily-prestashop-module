<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Model;

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
        $sql = new \DbQuery();
        $sql->select('c.`id_cart`, c.`id_customer`, c.`date_upd`, cu.`firstname`, cu.`lastname`, cu.`email`');
        $sql->from('cart', 'c');
        $sql->leftJoin('orders', 'o', 'o.`id_cart` = c.`id_cart`');
        $sql->rightJoin('customer', 'cu', 'cu.`id_customer` = c.`id_customer`');
        $sql->leftOuterJoin('smaily_cart', 'sc', 'c.`id_cart` = sc.`id_cart`');
        $sql->where('sc.`id_cart` IS NULL');
        $sql->where('DATE_SUB(CURDATE(),INTERVAL 10 DAY) <= c.date_add');
        $sql->where('o.`id_order` IS NULL');
        $sql->groupBy('cu.`id_customer`');
        $sql .= \Shop::addSqlRestriction(\Shop::SHARE_CUSTOMER, 'c');

        $carts = $this->db->executeS($sql);

        $result = [];
        foreach ($carts as $abandoned_cart) {
            $prestaCart = new \Cart($abandoned_cart['id_cart']);
            $products = $prestaCart->getProducts();
            // Don't continue if no products in cart.
            if (empty($products)) {
                continue;
            }

            $cart = new AbandonedCart();
            $cart->cartID = (int) $abandoned_cart['id_cart'];
            $cart->customerID = (int) $abandoned_cart['id_customer'];
            $cart->dateUpdated = $abandoned_cart['date_upd'];
            $cart->email = $abandoned_cart['email'];
            $cart->firstName = $abandoned_cart['firstname'];
            $cart->lastName = $abandoned_cart['lastname'];
            $cart->products = $products;

            $result[] = $cart;
        }

        return $result;
    }
}
