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
declare(strict_types=1);

class AdminSmailyforprestashopAjaxController extends ModuleAdminController
{
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
        if (Tools::getValue('ajax')
            && Tools::getValue('token') === Tools::getAdminTokenLite('AdminSmailyforprestashopAjax')
        ) {
            // If no subdomain entered display error message.
            if (!Tools::getValue('subdomain')
                || empty(trim(Tools::getValue('subdomain')))) {
                $response = ['error' => $this->l('Please enter subdomain!')];
                exit(Tools::jsonEncode($response));
            }
            // If no username entered display error message.
            if (!Tools::getValue('username')
                || empty(trim(Tools::getValue('subdomain')))) {
                $response = ['error' => $this->l('Please enter username!')];
                exit(Tools::jsonEncode($response));
            }
            // If no pasword entered display error message.
            if (!Tools::getValue('password')
                || empty(trim(Tools::getValue('password')))) {
                $response = ['error' => $this->l('Please enter password!')];
                exit(Tools::jsonEncode($response));
            }

            $subdomain = Tools::getValue('subdomain');
            // Normalize subdomain.
            // First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
            // if all else fails, then clean up subdomain and pass as is.
            if (filter_var($subdomain, FILTER_VALIDATE_URL)) {
                $url = parse_url($subdomain);
                $parts = explode('.', $url['host']);
                $subdomain = count($parts) >= 3 ? $parts[0] : '';
            } elseif (preg_match('/^[^\.]+\.sendsmaily\.net$/', $subdomain)) {
                $parts = explode('.', $subdomain);
                $subdomain = $parts[0];
            }
            $subdomain = preg_replace('/[^a-zA-Z0-9]+/', '', $subdomain);

            // Clean user entered subdomain.
            $subdomain = pSQL($subdomain);
            // Clean user entered username
            $username = pSQL(Tools::getValue('username'));
            $username = trim(Tools::stripslashes($username));
            // Clean user entered password.
            $password = pSQL(Tools::getValue('password'));
            $password = trim(Tools::stripslashes($password));
            // Make API call to Smaily to get autoresponders list.
            $response = $this->callApi(
                'workflows',
                $subdomain,
                $username,
                $password,
                [
                    'trigger_type' => 'form_submitted',
                ]
            );
            // Failsafe for empty response.
            if (!$response) {
                $response = ['error' => $this->l('Invalid login details!')];
                exit(Tools::jsonEncode($response));
            }
            // Add credentials to DB if successfully validated.
            if (array_key_exists('success', $response)) {
                Configuration::updateValue('SMAILY_SUBDOMAIN', $subdomain);
                Configuration::updateValue('SMAILY_USERNAME', $username);
                Configuration::updateValue('SMAILY_PASSWORD', $password);
            }
            exit(Tools::jsonEncode($response));
        }
    }

    public function ajaxProcessGetAutoresponders()
    {
        $response = [];
        // Validate token and if request is ajax call.
        if (Tools::getValue('ajax')
            && Tools::getValue('token') === Tools::getAdminTokenLite('AdminSmailyforprestashopAjax')
        ) {
            // Get credentials from db.
            $subdomain = pSQL(Configuration::get('SMAILY_SUBDOMAIN'));
            $username = pSQL(Configuration::get('SMAILY_USERNAME'));
            $password = pSQL(Configuration::get('SMAILY_PASSWORD'));
            // Make API call to Smaily to get autoresponders list.
            $response = $this->callApi(
                'workflows',
                $subdomain,
                $username,
                $password,
                [
                    'trigger_type' => 'form_submitted',
                ]
            );
            exit(Tools::jsonEncode($response));
        }
    }

    /**
     * Makes API call to Smaily api
     *
     * @param string $endpoint Smaily API endpoint without .php
     * @param string $subdomain Smaily account subdomain
     * @param string $username Smaily username
     * @param string $password Smaily password
     * @param array $data Data to be sent to Smaily
     * @param string $method GET or POST method
     *
     * @return array $result    Response from Smaily
     */
    public function callApi($endpoint, $subdomain, $username, $password, $data = [], $method = 'GET')
    {
        $apiUrl = 'https://' . $subdomain . '.sendsmaily.net/api/' . trim($endpoint, '/') . '.php';
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
        if (!curl_errno($ch)) {
            switch ((int) $http_status) {
                case 200:
                    return ['success' => true, 'autoresponders' => $result];
                case 401:
                    return $result = ['error' => $this->l('Check credentials, unauthorized!')];
                case 404:
                    return $result = ['error' => $this->l('Check subdomain, unauthorized!')];
                default:
                    return $result = ['error' => $this->l('Something went wrong with request to Smaily!')];
            }
        } else {
            return $result = ['error' => $this->l(curl_error($ch))];
        }
        curl_close($ch);
    }
}
