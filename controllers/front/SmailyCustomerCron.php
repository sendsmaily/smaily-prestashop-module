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
    /**
     * Limit unsubscribers request batch size.
     */
    const UNSUBSCRIBERS_BATCH_LIMIT = 1000;

    /**
     * Limit subscribers query batch size.
     */
    const SUBSCRIBERS_BATCH_LIMIT = 1000;

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
            $unsubscribers_emails = $this->getUnsubscribersEmails(self::UNSUBSCRIBERS_BATCH_LIMIT);
            // Get subscribed customers from store database.
            $customers = Db::getInstance()->executeS("Select * from "._DB_PREFIX_."customer WHERE newsletter=1");
            // Subscribed customers email array.
            $subsribed_customer_emails = array();
            if (!empty($customers)) {
                // Add customer emails to array.
                foreach ($customers as $customer) {
                    $subsribed_customer_emails[] = $customer['email'];
                }
                // Remove subscribed status for unsubscribers.
                foreach ($subsribed_customer_emails as $email) {
                    if (in_array($email, $unsubscribers_emails)) {
                        $query = 'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter=0 WHERE email="' .
                            pSQL($email) . '"';
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

                $chunks = array_chunk($update_data, self::SUBSCRIBERS_BATCH_LIMIT);
                foreach ($chunks as $chunk) {
                    // Send subscribers to Smaily.
                    $response = $this->module->callApi('contact', $chunk, 'POST');
                    // Response logging in case of error.
                    if (isset($response['result']['code']) && $response['result']['code'] !== 101) {
                        $this->module->logTofile('smaily-cron.txt', 'Customer sync failed - ' . $response['message']);
                        break;
                    }
                }
            }
            echo($this->l('User synchronization done!'));
        } else {
            echo($this->l('User synchronization disabled!'));
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
    private function getUnsubscribersEmails($limit = 1000)
    {
        $data = array(
            'list' => 2,
            'limit' => $limit,
            'offset' => 0,
        );
        $unsubscribers_emails = array();

        while (true) {
            // Api call to Smaily
            $unsubscribers = $this->module->callApi('contact', $data);

            // Stop if error.
            if (!isset($unsubscribers['success'])) {
                break;
            }
            // Stop if no more subscribers.
            if (empty($unsubscribers['result'])) {
                break;
            }

            foreach ($unsubscribers['result'] as $unsubscriber) {
                $unsubscribers_emails[] = $unsubscriber['email'];
            }
            // Smaily API call offset is considered as page number, not SQL offset!
            $data['offset']++;
        }

        return $unsubscribers_emails;
    }
}
