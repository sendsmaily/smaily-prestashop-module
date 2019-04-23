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

            $sql = 'SELECT c.id_cart,
                        c.id_customer,
                        c.date_upd,
                        cu.firstname,
                        cu.lastname,
                        cu.email
                    FROM '._DB_PREFIX_.'cart c
                    LEFT JOIN '._DB_PREFIX_.'orders o
                    ON (o.id_cart = c.id_cart)
                    RIGHT JOIN '._DB_PREFIX_.'customer cu
                    ON (cu.id_customer = c.id_customer)
                    WHERE DATE_SUB(CURDATE(),INTERVAL 10 DAY) <= c.date_add
                    AND o.id_order IS NULL';

            $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');
            $sql .= ' GROUP BY cu.id_customer';

            $abandoned_carts = Db::getInstance()->executeS($sql);

            foreach ($abandoned_carts as $abandoned_cart) {
                // If time has passed form last cart update
                $cart_updated_time = strtotime($abandoned_cart['date_upd']);
                $reminder_time = strtotime('+' . $delay . ' hours', $cart_updated_time);
                $current_time = strtotime(date('Y-m-d H:i') . ':00');

                // Check if mail has allready been sent about this cart
                $id_customer = (int) $abandoned_cart['id_customer'];
                $id_cart = (int) $abandoned_cart['id_cart'];
                $email_sent = $this->checkEmailSent($id_cart);

                if ($current_time >= $reminder_time && !$email_sent) {
                    $cart = new Cart($abandoned_cart['id_cart']);
                    $products = $cart->getProducts();

                    $adresses = array(
                        'email' => $abandoned_cart['email'],
                        'firstname' => $abandoned_cart['firstname'],
                        'lastname' => $abandoned_cart['lastname'],
                    );

                    // Collect products of abandoned cart.
                    if (!empty($products)) {
                        $i = 1;
                        foreach ($products as $product) {
                            if ($i <= 10) {
                                foreach ($sync_fields as $sync_field) {
                                    $adresses['product_' . $sync_field .'_' . $i] = strip_tags($product[$sync_field]);
                                }
                            }
                            $i++;
                        }
                        // Add shop url.
                        $adresses['store_url'] = _PS_BASE_URL_.__PS_BASE_URI__;
                        // Smaily api query.
                        $query = array(
                            'autoresponder' => $autoresponder_id,
                            'addresses' => array($adresses)
                        );
                        // Send cart data to smaily api.
                        $response = $this->callApi('autoresponder', $query, 'POST');
                        // If email sent successfully update sent status in database.
                        if (array_key_exists('success', $response) &&
                            isset($response['result']['code']) &&
                            $response['result']['code'] === 101) {
                                $this->updateSentStatus($id_customer, $id_cart);
                        } else {
                            $this->logTofile('smaily-cart.txt', Tools::jsonEncode($response));
                        }
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

    /**
     * Check if abandoned cart reminder email has been sent to customer
     *
     * @param int $cart_id                      Customer cart ID
     * @return boolean $abandoned_cart_email    If mail has been sent
     */
    private function checkEmailSent(int $id_cart)
    {
        $sql = 'SELECT *
                FROM '._DB_PREFIX_.'smaily_cart 
                WHERE id_cart = '. $id_cart;

        $email_sent = Db::getInstance()->executeS($sql);
        if (!$email_sent) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Makes API call to Smaily.
     *
     * @param string $endpoint  Endpoint of smaily API without .php
     * @param array $data       Data to be sent to API.
     * @param string $method    'GET' or 'POST' method.
     * @return array $response  Response from smaily api.
     */
    private function callApi(string $endpoint, array $data, string $method = 'GET')
    {
        // Smaily api credentials.
        $subdomain = pSQL(Configuration::get('SMAILY_SUBDOMAIN'));
        $username = pSQL(Configuration::get('SMAILY_USERNAME'));
        $password = pSQL(Configuration::get('SMAILY_PASSWORD'));

        // API call.
        $apiUrl = "https://" . $subdomain . ".sendsmaily.net/api/" . trim($endpoint, '/') . ".php";
        $data = http_build_query($data);
        if ($method == 'GET') {
            $apiUrl = $apiUrl.'?'.$data;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = json_decode(curl_exec($ch), true);
        // Error handling
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ((int) $http_status === 401) {
            return $result = array('error' => $this->l('Check credentials, unauthorized!'));
        }
        if (curl_errno($ch)) {
            return $result = array("error" => curl_error($ch));
        }
        // Close connection and send response.
        curl_close($ch);
        return array('success' => true, 'result' => $result);
    }
    /**
     * Log API response to text-file.
     *
     * @param string $filename  Name of the file created.
     * @param string $msg       Text response from api.
     * @return void
     */
    private function logToFile($filename, $response)
    {
        $logger = new FileLogger(1);
        $logger->setFilename(_PS_MODULE_DIR_. $this->module->name ."/" . $filename);
        $logger->logInfo('Response from API - ' . $response);
    }
}
