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
            $subdomain = str_replace(' ', '', Tools::stripslashes($subdomain));
            // Clean user entered username
            $username = pSQL(Tools::getValue('username'));
            $username = str_replace(' ', '', Tools::stripslashes($username));
            // Clean user entered password.
            $password = pSQL(Tools::getValue('password'));
            $password = str_replace(' ', '', Tools::stripslashes($password));
            // Autoresponder array for smaily Api call.
            $data = array('page'=>1,'limit'=>100,'status'=>array('ACTIVE'));
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

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ((int) $http_status === 401) {
            return $result = array('error' => $this->l('Check credentials, unauthorized!'));
        }

        if (curl_errno($ch)) {
            return $result = array("error"=>true,"message"=>curl_error($ch));
        }
        curl_close($ch);
        return array('success' =>true, 'autoresponders' => $result);
    }
}
