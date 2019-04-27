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

class SmailyforprestashopSmailyCustomerCronModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        header('Content-Type: text/plain');
        if (Tools::getValue('token') == Configuration::get('SMAILY_CUSTOMER_CRON_TOKEN')) {
            $this->syncContacts();
            die();
        } else {
            die($this->l('Access denied! '));
        }
    }

    /**
     * Synchronize prestashop contacts with smaily database.
     * @return void
     */
    private function syncContacts()
    {
        if (Configuration::get('SMAILY_ENABLE_CRON') === "1") {
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

            // TODO: Benchmark if its faster to query again or remove unsubscribers from previous array.
            // Get subscribed customers from db after removal of unsubscribers.
            $customers = Db::getInstance()->executeS("Select * from "._DB_PREFIX_."customer WHERE newsletter=1");
            // Send subscribed customer data to smaily based on settings from.
            $update_data = array();
            if (!empty($customers)) {
                foreach ($customers as $customer) {
                    $userdata = $this->getUserData($customer);
                    array_push($update_data, $userdata);
                }
                // Send subscribers to Smaily.
                $response = $this->module->callApi('contact', $update_data, 'POST');
                // Response logging.
                if (isset($response['success'])) {
                    $response = $response['result']['message'];
                } else {
                    $response =  $response['error'];
                }
            } else {
                $response = 'No customers to update!';
            }
            $this->module->logTofile('smaily-cron.txt', $response);
            echo($this->l('User synchronization done! '));
        } else {
            echo($this->l('User synchronization disabled! '));
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
        $response = $this->module->callApi('contact', $data);
        // If successful return unsubscribers.
        if (isset($response['success'])) {
            return $response['result'];
        // If has errors save errors to log.
        } else {
            $this->module->logTofile('smaily-cron.txt', $response['error']);
            return array();
        }
    }
}
