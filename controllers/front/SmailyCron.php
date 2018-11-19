<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
                        $query = 'UPDATE '._DB_PREFIX_.'customer SET newsletter=0 WHERE email="'.pSQL($customer).'"';
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
