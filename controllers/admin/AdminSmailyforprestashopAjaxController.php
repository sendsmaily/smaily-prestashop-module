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

class AdminSmailyforprestashopAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
    }

    /**
     * Receives information from settings form and returns autoresponders as
     * Select Autoresponder options.
     *
     * @return void
     */
    public function ajaxProcessSmailyValidate()
    {
        $response = null;
        // Validate token and if request is ajax call.
        if (Tools::getValue('ajax') &&
            Tools::getValue('token') === Tools::getAdminTokenLite('AdminSmailyforprestashopAjax')
            ) {
            // If no subdomain entered display error message.
            if (!Tools::getValue('subdomain') ||
                empty(trim(Tools::getValue('subdomain')))) {
                    $response = array('error' => $this->l('Please enter subdomain!'));
                    die(Tools::jsonEncode($response));
            }
            // If no username entered display error message.
            if (!Tools::getValue('username') ||
                empty(trim(Tools::getValue('subdomain')))) {
                    $response = array('error' => $this->l('Please enter username!'));
                    die(Tools::jsonEncode($response));
            }
            // If no pasword entered display error message.
            if (!Tools::getValue('password') ||
                empty(trim(Tools::getValue('password')))) {
                    $response = array('error' => $this->l('Please enter password!'));
                    die(Tools::jsonEncode($response));
            }
            // Clean user entered subdomain.
            $subdomain = pSQL(Tools::getValue('subdomain'));
            $subdomain = trim(Tools::stripslashes($subdomain));
            // Clean user entered username
            $username = pSQL(Tools::getValue('username'));
            $username = trim(Tools::stripslashes($username));
            // Clean user entered password.
            $password = pSQL(Tools::getValue('password'));
            $password = trim(Tools::stripslashes($password));
            // Autoresponder array for smaily Api call.
            $data = array('page' => 1, 'limit' => 100, 'status' => array('ACTIVE'));
            // Make API call to Smaily to get autoresponders list.
            $response = $this->callApi('autoresponder', $subdomain, $username, $password, $data);
            if (!$response) {
                $response = array('error' => $this->l('Invalid login details!'));
                die(Tools::jsonEncode($response));
            }
            die(Tools::jsonEncode($response));
        }
    }

    /**
     * Makes API call to Smaily api
     *
     * @param string $endpoint  Smaily API endpoint without .php
     * @param string $subdomain Smaily account subdomain
     * @param string $username  Smaily username
     * @param string $password  Smaily password
     * @param array $data       Data to be sent to Smaily
     * @param string $method    GET or POST method
     * @return array $result    Response from Smaily
     */
    public function callApi($endpoint, $subdomain, $username, $password, $data, $method = 'GET')
    {

        $apiUrl = "https://" . $subdomain . ".sendsmaily.net/api/" . trim($endpoint, '/') . ".php";
        $data = http_build_query($data);
        if ($method == 'GET') {
            $apiUrl = $apiUrl . '?' . $data;
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

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ((int) $http_status === 401) {
            return $result = array('error' => $this->l('Check credentials, unauthorized!'));
        }

        if (curl_errno($ch)) {
            return $result = array("error" => true, "message" => curl_error($ch));
        }
        curl_close($ch);
        return array('success' => true, 'autoresponders' => $result);
    }
}
