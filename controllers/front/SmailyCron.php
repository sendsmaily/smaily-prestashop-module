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
            return $result = array("error"=>curl_error($ch));
        }
        // Close connection and send response.
        curl_close($ch);
        return array('success' =>true, 'result' => $result);
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
