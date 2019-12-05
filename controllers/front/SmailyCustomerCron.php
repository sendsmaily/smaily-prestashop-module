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

        if (Tools::getValue('token') != Configuration::get('SMAILY_CUSTOMER_CRON_TOKEN')) {
            die($this->l('Access denied! '));
        }
    
        if ((int) Configuration::get('SMAILY_ENABLE_CRON') !== 1) {
            die($this->l('User synchronization disabled!'));
        }

        if ($this->syncContacts()) {
            die($this->l('User synchronization done!'));
        } else {
            die($this->l('User synchronization failed!'));
        }
    }

    /**
     * Synchronize prestashop contacts with Smaily database.
     * @return bool Success status.
     */
    private function syncContacts()
    {
        $unsubscribers_synchronized = $this->removeUnsubscribers(self::UNSUBSCRIBERS_BATCH_LIMIT);
        if (!$unsubscribers_synchronized) {
            $this->module->logTofile('smaily-cron.txt', 'Customer sync failed - unsubscribers are not removed');
            return false;
        }

        // Don't sync customers if failed to remove unsubscribers.
        $subscribers_synchronized = $this->sendSubscribersToSmaily(self::SUBSCRIBERS_BATCH_LIMIT);
        if (!$subscribers_synchronized) {
            $this->module->logTofile('smaily-cron.txt', 'Customer sync failed - faild to send subscribers to Smaily');
            return false;
        }

        return true;
    }

    /**
     * Get user data for customer based on settings for Syncronize Additional.
     *
     * @param array $customer   Customer array from Presta DB.
     * @param array $fields     Additional synchronisation fields from settings.
     *
     * @return array $userdata  Customer field values based of settings in Syncronize Additional.
     */
    private function getUserData($customer, $fields)
    {
        $userdata = array();

        if (!empty($fields)) {
            foreach ($fields as $sync_data) {
                if (isset($customer[$sync_data])) {
                    $userdata[$sync_data] = $customer[$sync_data];
                }
            }
        }

        $userdata['email'] = $customer['email'];
        return $userdata;
    }

    /**
     * Get unsubscribers from Smaily and change subscription status to unsubscribed in store.
     *
     * @param int $limit Limit request size.
     *
     * @return bool Success status.
     */
    private function removeUnsubscribers($limit = 1000)
    {
        $offset = 0;

        while (true) {
            $unsubscribers = $this->module->callApi(
                'contact',
                array(
                    'list' => 2,
                    'limit' => $limit,
                    'offset' => $offset,
                )
            );

            // Stop if error.
            if (!isset($unsubscribers['success'])) {
                return false;
            }
            // Stop if no more subscribers.
            if (empty($unsubscribers['result'])) {
                break;
            }

            // Remove subscribed status for unsubscribers.
            $query = 'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter=0 WHERE email IN (' .
            implode(
                ', ',
                array_map(function ($item) {
                    return "'" . pSQL($item['email']) . "'";
                },
                $unsubscribers['result'])
            ) . ')';
            $query_result = Db::getInstance()->execute($query);
            // Stop if query fails.
            if ($query_result === false) {
                return false;
            }

            // Smaily API call offset is considered as page number, not SQL offset!
            $offset++;
        }

        return true;
    }

    /**
     * Send store subscribers data to Smaily.
     *
     * @param int $limit subscriber request batch limit.
     * @return bool Success status.
     */
    public function sendSubscribersToSmaily($limit)
    {
        $offset = 0;
        $additional_fields = unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'));

        while (true) {
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('customer', 'c');
            $sql->where('c.newsletter = 1');
            $sql->limit(strval($limit), $offset);

            $customers = Db::getInstance()->executeS($sql);
            // Stop if query fails.
            if ($customers === false) {
                return false;
            }
            // Stop if no more qustomers.
            if (empty($customers)) {
                break;
            }

            $update_data = array();
            foreach ($customers as $customer) {
                $userdata = $this->getUserData($customer, $additional_fields);
                array_push($update_data, $userdata);
            }

            // Send subscribers to Smaily.
            $response = $this->module->callApi('contact', $update_data, 'POST');
            // Stop if not successful update.
            if (isset($response['result']['code']) && $response['result']['code'] !== 101) {
                return false;
            }

            $offset += $limit;
        }

        return true;
    }
}
