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

class SmailyforprestashopSmailyCartCronModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        parent::init();
        header('Content-Type: text/plain');
        if (Tools::getValue('token') == Configuration::get('SMAILY_CART_CRON_TOKEN')) {
            $this->abandonedCart();
            die();
        } else {
            die($this->l('Access denied! '));
        }
    }

    /**
     * Send abandoned cart emails to customers.
     *
     * @return void
     */
    private function abandonedCart()
    {
        if (Configuration::get('SMAILY_ENABLE_ABANDONED_CART') === "1") {
            // Settings
            $autoresponder = unserialize(Configuration::get('SMAILY_CART_AUTORESPONDER'));
            $autoresponder_id = pSQL($autoresponder['id']);
            $delay = pSQL(Configuration::get('SMAILY_ABANDONED_CART_TIME'));
            // Values to sync array
            $sync_fields = unserialize(Configuration::get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'));

            // Select all carts where cart_id is not on orders table and exclude if excists in smaily_cart table.
            // Gather customer data also.
            $sql = 'SELECT c.id_cart,
                        c.id_customer,
                        c.date_upd,
                        cu.firstname,
                        cu.lastname,
                        cu.email
                    FROM ' . _DB_PREFIX_ . 'cart c
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders o
                    ON (o.id_cart = c.id_cart)
                    RIGHT JOIN ' . _DB_PREFIX_ . 'customer cu
                    ON (cu.id_customer = c.id_customer)
                    LEFT OUTER JOIN ' . _DB_PREFIX_ . 'smaily_cart sc
                    ON (c.id_cart = sc.id_cart)
                    WHERE sc.id_cart IS NULL
                    AND DATE_SUB(CURDATE(),INTERVAL 10 DAY) <= c.date_add
                    AND o.id_order IS NULL';

            $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');
            $sql .= ' GROUP BY cu.id_customer';

            $abandoned_carts = Db::getInstance()->executeS($sql);
            foreach ($abandoned_carts as $abandoned_cart) {
                // If time has passed form last cart update
                $cart_updated_time = strtotime($abandoned_cart['date_upd']);
                $reminder_time = strtotime('+' . $delay . ' minutes', $cart_updated_time);
                $current_time = strtotime(date('Y-m-d H:i') . ':00');

                // Check if delay passed to send cart.
                $id_customer = (int) $abandoned_cart['id_customer'];
                $id_cart = (int) $abandoned_cart['id_cart'];

                if ($current_time >= $reminder_time) {
                    $cart = new Cart($abandoned_cart['id_cart']);
                    $products = $cart->getProducts();

                    // Dont continue if no products in cart.
                    if (empty($products)) {
                        continue;
                    }

                    $adresses = array(
                        'email' => $abandoned_cart['email'],
                        'firstname' => $abandoned_cart['firstname'],
                        'lastname' => $abandoned_cart['lastname'],
                        'store_url' => _PS_BASE_URL_.__PS_BASE_URI__,
                    );
                    // Populate abandoned cart with empty values for legacy api.
                    $fields_available = array(
                        'name',
                        'description_short',
                        'price',
                        'category',
                        'quantity'
                    );
                    foreach ($fields_available as $field) {
                        for ($i=1; $i<=10; $i++) {
                            $adresses['product_' . $field . '_' . $i] = '';
                        }
                    }
                    // Collect products of abandoned cart.
                    $count = 1;
                    foreach ($products as $product) {
                        if ($count <= 10) {
                            foreach ($sync_fields as $sync_field) {
                                $adresses['product_' . $sync_field .'_' . $count] = strip_tags($product[$sync_field]);
                            }
                        }
                        $count++;
                    }
                    // Smaily api query.
                    $query = array(
                        'autoresponder' => $autoresponder_id,
                        'addresses' => array($adresses)
                    );
                    // Send cart data to smaily api.
                    $response = $this->module->callApi('autoresponder', $query, 'POST');
                    // If email sent successfully update sent status in database.
                    if (array_key_exists('success', $response) &&
                        isset($response['result']['code']) &&
                        $response['result']['code'] === 101) {
                            $this->updateSentStatus($id_customer, $id_cart);
                    } else {
                        $this->module->logTofile('smaily-cart.txt', Tools::jsonEncode($response));
                    }

                }
            }
            echo($this->l('Abandoned carts emails sent!'));
        } else {
            echo($this->l('Abandoned cart disabled!'));
        }
    }

    /**
     * Updates Sent email status in smaily cart table.
     *
     * @param integer $id_customer  Customer ID
     * @param integer $id_cart      Cart ID
     * @return void
     */
    private function updateSentStatus(int $id_customer, int $id_cart)
    {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'smaily_cart (id_customer, id_cart, date_sent)
                VALUES (' . $id_customer . ', ' . $id_cart . ', CURRENT_TIMESTAMP)';
        Db::getInstance()->execute($sql);
    }
}
