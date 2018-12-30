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

class SmailyforprestashopSmailyCronModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        $this->syncContacts();
        $this->abandonedCart();
    }

    /**
     * Synchronize prestashop contacts with smaily database.
     * @return void
     */
    private function syncContacts()
    {
        if (Tools::getValue('token') == Configuration::get('SMAILY_CRON_TOKEN') &&
            Configuration::get('SMAILY_ENABLE_CRON') === "1") {
            /**
             * Get unsubscribers from smaily database and unsubscribe these users in Prestashop.
             */
            // Get unsubscribers from smaily.
            $unsubscribers = $this->getUnsubscribers();
            // Unsubscribed emails array
            $unsubscribers_email = array();
            if (!empty($unsubscribers)) {
                foreach ($unsubscribers as $unsubscriber) {
                    $unsubscribers_email[] = $unsubscriber['email'];
                }
            }
            // Get subscribed customers from store database.
            $customers = Db::getInstance()->executeS("Select * from "._DB_PREFIX_."customer WHERE newsletter=1");
            // Subscribed customers email array.
            $customers_subscribed = array();
            if (!empty($customers)) {
                // Add customer emails to array.
                foreach ($customers as $customer) {
                    $customers_subscribed[] = $customer['email'];
                }
                // Remove subscribed status for unsubscribers.
                foreach ($customers_subscribed as $customer) {
                    if (in_array($customer, $unsubscribers_email)) {
                        $query = 'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter=0 WHERE email="' .
                                pSQL($customer) . '"';
                        Db::getInstance()->execute($query);
                    }
                }
            }
            /**
             * Sends subscribed customer data to smaily based on settings from
             * configuration page
             */
            $update_data = array();
            if (!empty($customers)) {
                foreach ($customers as $customer) {
                    $userdata = $this->getUserData($customer);
                    array_push($update_data, $userdata);
                }
                // Send subscribers to Smaily.
                $response = $this->callApi('contact', $update_data, 'POST');
                // Response logging.
                if (isset($response['success'])) {
                    $response = $response['result']['message'];
                } else {
                    $response =  $response['error'];
                }
            } else {
                $response = 'No customers to update!';
            }
            $this->logTofile('smaily-cron.txt', $response);
        } else {
            die($this->l('Access denied or cron disabled!'));
        }
    }

    /**
     * Send abandoned cart emails to customers.
     *
     * @return void
     */
    private function abandonedCart()
    {
        if (Tools::getValue('token') == Configuration::get('SMAILY_CRON_TOKEN') &&
            Configuration::get('SMAILY_ENABLE_ABANDONED_CART') === "1") {
            // Settings
            $autoresponder = stripslashes(pSQL((Configuration::get('SMAILY_CART_AUTORESPONDER'))));
            $autoresponder = unserialize($autoresponder);
            $delay = pSQL(Configuration::get('SMAILY_ABANDONED_CART_TIME'));
            // Values to sync array
            $sync_fields = ['name', 'description_short', 'price', 'category', 'quantity'];

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

                    $adresses = [
                        'email' => $abandoned_cart['email'],
                        'firstname' => $abandoned_cart['firstname'],
                        'lastname' => $abandoned_cart['lastname'],
                    ];

                    // Collect products of abandoned cart.
                    if (!empty($products)) {
                        $i = 1;
                        foreach ($products as $product) {
                            if ($i <= 10) {
                                foreach ($sync_fields as $sync_field) {
                                    $adresses['product_' . $sync_field .'_' . $i] = $product[$sync_field];
                                }
                            }
                            $i++;
                        }
                        // Add shop url.
                        $adresses['store_url'] = _PS_BASE_URL_.__PS_BASE_URI__;
                        // Smaily api query.
                        $query = [
                            'autoresponder' => $autoresponder['id'],
                            'addresses' => [$adresses]
                        ];
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
        } else {
            die($this->l('Access denied or cron disabled!'));
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
     * Get user data for costumer based on settings for Syncronize Additional.
     *
     * @param array $customer   Customer array from Presta DB.
     * @return array $userdata  Customer field values based of settings in Syncronize Additional.
     */
    private function getUserData($customer)
    {
        $userdata = array();
        $syncronize_additonal = unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'));
        if (!empty($syncronize_additonal)) {
            foreach ($syncronize_additonal as $sync_data) {
                $userdata[pSQL($sync_data)] = $customer[pSQL($sync_data)];
            }
        }
        $userdata['email'] = pSQL($customer['email']);
        return $userdata;
    }

    /**
     * Get unsubscribed users list from Smaily.
     *
     * @return array $unsubscribers Unsubscribers list from Smaily.
     */
    private function getUnsubscribers()
    {
        $data = array(
            'list' => 2,
        );
        // Api call to Smaily
        $response = $this->callApi('contact', $data);
        // If successful return unsubscribers.
        if (isset($response['success'])) {
            return $response['result'];
        // If has errors save errors to log.
        } else {
            $this->logTofile('smaily-cron.txt', $response['error']);
            return array();
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
