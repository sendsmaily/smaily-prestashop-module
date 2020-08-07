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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmailyForPrestashop extends Module
{
    public function __construct()
    {
        $this->name = 'smailyforprestashop';
        $this->tab = 'advertising_marketing';
        $this->version = '1.3.0';
        $this->author = 'Smaily';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7.0',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;

        parent::__construct();
        $this->controllerAdmin = 'AdminSmailyForPrestashopAjax';

        $this->displayName = $this->l('Smaily for Prestashop');
        $this->description = $this->l('Smaily email marketing and automation module for PrestaShop.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        // Check if multistore is enabled
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            // Check that the module can be attached to the header hook.
            !$this->registerHook('backOfficeHeader') ||
            // Check that you can add Smaily settings field values.
            !Configuration::updateValue('SMAILY_ENABLE_CRON', 0) ||
            !Configuration::updateValue('SMAILY_ENABLE_ABANDONED_CART', 0) ||
            !Configuration::updateValue('SMAILY_CUSTOMER_CRON_TOKEN', '') ||
            !Configuration::updateValue('SMAILY_CART_CRON_TOKEN', '') ||
            !Configuration::updateValue('SMAILY_SUBDOMAIN', '') ||
            !Configuration::updateValue('SMAILY_USERNAME', '') ||
            !Configuration::updateValue('SMAILY_PASSWORD', '') ||
            !Configuration::updateValue('SMAILY_CART_AUTORESPONDER', '') ||
            !Configuration::updateValue('SMAILY_ABANDONED_CART_TIME', '') ||
            !Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize(array())) ||
            !Configuration::updateValue('SMAILY_CART_SYNCRONIZE_ADDITIONAL', serialize(array())) ||
            !Configuration::updateValue('SMAILY_RSS_CATEGORY_ID', 'all_products') ||
            !Configuration::updateValue('SMAILY_RSS_LIMIT', '50') ||
            !Configuration::updateValue('SMAILY_RSS_ORDER_BY', 'date_upd') ||
            !Configuration::updateValue('SMAILY_RSS_ORDER_WAY', 'desc') ||
            // Add tab to sidebar
            !$this->installTab('AdminAdmin', 'AdminSmailyforprestashopAjax', 'Smaily for PrestaShop') ||
            // Add Newsletter subscription form.
            !$this->registerHook('footerBefore') ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('rightColumn')
        ) {
            return false;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'smaily_cart (
                `id_smaily_cart` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `id_customer` INT UNSIGNED NULL ,
                `id_cart` INT UNSIGNED NULL ,
                `date_sent` DATETIME NOT NULL) ENGINE='._MYSQL_ENGINE_;
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    public function installTab($parent_class, $class_name, $name)
    {
        $tab = new Tab();
        $tab->id_parent = (int) Tab::getIdFromClassName($parent_class);
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        $tab->class_name = $class_name;
        $tab->module = $this->name;
        $tab->active = 0;
        return $tab->add();
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
        // Delete settings created by module.
        !Configuration::deleteByName('SMAILY_ENABLE_CRON') ||
        !Configuration::deleteByName('SMAILY_ENABLE_ABANDONED_CART') ||
        !Configuration::deleteByName('SMAILY_CUSTOMER_CRON_TOKEN') ||
        !Configuration::deleteByName('SMAILY_CART_CRON_TOKEN') ||
        !Configuration::deleteByName('SMAILY_SUBDOMAIN') ||
        !Configuration::deleteByName('SMAILY_USERNAME') ||
        !Configuration::deleteByName('SMAILY_PASSWORD') ||
        !Configuration::deleteByName('SMAILY_CART_AUTORESPONDER') ||
        !Configuration::deleteByName('SMAILY_ABANDONED_CART_TIME') ||
        !Configuration::deleteByName('SMAILY_SYNCRONIZE_ADDITIONAL') ||
        !Configuration::deleteByName('SMAILY_CART_SYNCRONIZE_ADDITIONAL') ||
        !Configuration::deleteByName('SMAILY_RSS_CATEGORY_ID') ||
        !Configuration::deleteByName('SMAILY_RSS_LIMIT') ||
        !Configuration::deleteByName('SMAILY_RSS_ORDER_BY') ||
        !Configuration::deleteByName('SMAILY_RSS_ORDER_WAY') ||
        // Remove sideTab of smaily module.
        !$this->uninstallTab('AdminSmailyforprestashopAjax')
        ) {
            return false;
        }
        Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'smaily_cart');
        return true;
    }

    public function uninstallTab($class_name)
    {
        // Retrieve Tab ID
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        // Load tab
        $tab = new Tab((int)$id_tab);
        // Delete it
        return $tab->delete();
    }

    public function getContent()
    {
        $output = null;
        $this->context->controller->addJquery();

        // Remove credentials button.
        if (Tools::isSubmit('smaily_remove_credentials')) {
            if (Configuration::updateValue('SMAILY_SUBDOMAIN', '') &&
                Configuration::updateValue('SMAILY_USERNAME', '') &&
                Configuration::updateValue('SMAILY_PASSWORD', '')
            ) {
                // Disable customer sync.
                Configuration::updateValue('SMAILY_ENABLE_CRON', 0);
                // Disable abandoned cart cron and remove autoresponder.
                Configuration::updateValue('SMAILY_ENABLE_ABANDONED_CART', 0);
                Configuration::updateValue('SMAILY_CART_AUTORESPONDER', '');
                // Return success message.
                $output .= $this->displayConfirmation($this->l('Credentials removed!'));
            } else {
                // Return error message
                $output .= $this->displayError($this->l('Something went wrong removing credentials'));
            }
        }

        // Customer sync form.
        if (Tools::isSubmit('smaily_submit_configuration')) {
            // Enable Cron.
            $enable_cron = pSQL(Tools::getValue('SMAILY_ENABLE_CRON'));
            // Customer cron token.
            $customer_cron_token = pSQL(Tools::getValue('SMAILY_CUSTOMER_CRON_TOKEN'));
            $customer_cron_token = trim(Tools::stripslashes($customer_cron_token));
            if (empty($customer_cron_token)) {
                $customer_cron_token = uniqid();
            }

            // Syncronize additional.
            $syncronize_additional = Tools::getValue('SMAILY_SYNCRONIZE_ADDITIONAL');
            $escaped_sync_additional = array();
            if (!empty($syncronize_additional)) {
                foreach ($syncronize_additional as $key => $value) {
                    $escaped_sync_additional[] = pSQL($value);
                }
            }
            // Check if subdomain is saved to db to verify that credentials are validated.
            if (empty(Configuration::get('SMAILY_SUBDOMAIN'))) {
                // Display error message.
                $output .= $this->displayError($this->l('Please validate credentials before saving.'));
            } else {
                // Update settings.
                Configuration::updateValue('SMAILY_ENABLE_CRON', $enable_cron);
                Configuration::updateValue('SMAILY_CUSTOMER_CRON_TOKEN', $customer_cron_token);
                Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize($escaped_sync_additional));
                // Display success message.
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // Abandoned cart form.
        if (Tools::isSubmit('smaily_submit_abandoned_cart')) {
            // Enable Abandoned Cart.
            $enable_abandoned_cart = pSQL(Tools::getValue('SMAILY_ENABLE_ABANDONED_CART'));
            // Abandoned cart delay time
            $abandoned_cart_time = pSQL(Tools::getValue('SMAILY_ABANDONED_CART_TIME'));
            $abandoned_cart_time = (int) trim(Tools::stripslashes($abandoned_cart_time));
            // Cart cron token.
            $cart_cron_token = pSQL(Tools::getValue('SMAILY_CART_CRON_TOKEN'));
            $cart_cron_token = trim(Tools::stripslashes($cart_cron_token));
            if (empty($cart_cron_token)) {
                $cart_cron_token = uniqid();
            }
            // Abandoned cart Autoresponder
            $cart_autoresponder = pSQL((Tools::getValue('SMAILY_CART_AUTORESPONDER')));
            $cart_autoresponder = str_replace('\"', '"', $cart_autoresponder);
            // Get autoresponder array from json string.
            $cart_autoresponder = Tools::jsonDecode($cart_autoresponder);
            // Clean autoresponder for inserting to database.
            $escaped_cart_autoresponder = array();
            if (!empty($cart_autoresponder)) {
                foreach ($cart_autoresponder as $key => $value) {
                    $escaped_cart_autoresponder[ pSQL($key)] = pSQL($value);
                }
            }
            // Syncronize additional for abandoned cart template.
            $cart_syncronize_additional = Tools::getValue('SMAILY_CART_SYNCRONIZE_ADDITIONAL');
            $cart_escaped_sync_additional = array();
            if (!empty($cart_syncronize_additional)) {
                foreach ($cart_syncronize_additional as $key => $value) {
                    $cart_escaped_sync_additional[] = pSQL($value);
                }
            }
            // Validate autoresponder time and autoresponder for cart.
            if ($abandoned_cart_time < 15) {
                // Display error message.
                $output .= $this->displayError($this->l('Abandoned cart delay has to be atleast 15 minutes.'));
            } elseif ((int)$enable_abandoned_cart === 1 && empty($cart_autoresponder)) {
                // Display error message.
                $output .= $this->displayError($this->l('Select autoresponder for abandoned cart.'));
            } else {
                Configuration::updateValue('SMAILY_ENABLE_ABANDONED_CART', $enable_abandoned_cart);
                Configuration::updateValue('SMAILY_CART_AUTORESPONDER', serialize($escaped_cart_autoresponder));
                Configuration::updateValue('SMAILY_ABANDONED_CART_TIME', $abandoned_cart_time);
                Configuration::updateValue('SMAILY_CART_CRON_TOKEN', $cart_cron_token);
                Configuration::updateValue(
                    'SMAILY_CART_SYNCRONIZE_ADDITIONAL',
                    serialize($cart_escaped_sync_additional)
                );
                // Display success message.
                $output .= $this->displayConfirmation($this->l('Abandoned cart settings updated'));
            }
        }
        // RSS
        if (Tools::isSubmit('smaily_submit_rss')) {
            $category_id = pSQL(Tools::getValue('SMAILY_RSS_CATEGORY_ID'));
            $limit = pSQL(Tools::getValue('SMAILY_RSS_LIMIT'));
            $order_by = pSQL(Tools::getValue('SMAILY_RSS_ORDER_BY'));
            $order_way = pSQL(Tools::getValue('SMAILY_RSS_ORDER_WAY'));

            // Check if subdomain is saved to db to verify that credentials are validated.
            if (empty(Configuration::get('SMAILY_SUBDOMAIN'))) {
                // Display error message.
                $output .= $this->displayError($this->l('Please validate credentials before saving.'));
            } else {
                // Update settings.
                Configuration::updateValue('SMAILY_RSS_CATEGORY_ID', $category_id);
                Configuration::updateValue('SMAILY_RSS_LIMIT', $limit);
                Configuration::updateValue('SMAILY_RSS_ORDER_BY', $order_by);
                Configuration::updateValue('SMAILY_RSS_ORDER_WAY', $order_way);
                // Display success message.
                $output .= $this->displayConfirmation($this->l('RSS settings updated'));
            }
        }

        // Get syncronize additional values for template.
        if (false !== unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'))) {
            $sync_array = unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'));
        } else {
            $sync_array = array();
        }
        // Get abandoned cart syncronize additional values for template.
        if (false !== unserialize(Configuration::get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'))) {
            $cart_sync_array = unserialize(Configuration::get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'));
        } else {
            $cart_sync_array = array();
        }
        // Get customer cron token or generate random string when not set.
        if (false != Configuration::get('SMAILY_CUSTOMER_CRON_TOKEN')) {
            $customer_cron_token = pSQL(Configuration::get('SMAILY_CUSTOMER_CRON_TOKEN'));
        } else {
            $customer_cron_token =  uniqid();
        }
        // Get cart cron token or generate random string when not set.
        if (false != Configuration::get('SMAILY_CART_CRON_TOKEN')) {
            $cart_cron_token = pSQL(Configuration::get('SMAILY_CART_CRON_TOKEN'));
        } else {
            $cart_cron_token = uniqid();
        }
        // Get abandoned cart autoresponder values for template.
        $cart_autoresponder_for_template = pSQL((Configuration::get('SMAILY_CART_AUTORESPONDER')));
        $cart_autoresponder_for_template = str_replace('\"', '"', $cart_autoresponder_for_template);
        $cart_autoresponder_for_template = unserialize($cart_autoresponder_for_template);
        //
        $categories = Category::getNestedCategories(null, Context::getContext()->language->id);
        //
        // Assign variables to template if available.
        $this->context->smarty->assign(
            array(
            'smaily_enable_cron' =>  pSQL(Configuration::get('SMAILY_ENABLE_CRON')),
            'smaily_enable_abandoned_cart' => pSQL(Configuration::get('SMAILY_ENABLE_ABANDONED_CART')),
            'smaily_customer_cron_token' => $customer_cron_token,
            'smaily_cart_cron_token' => $cart_cron_token,
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
            'smaily_username' => pSQL(Configuration::get('SMAILY_USERNAME')),
            'smaily_password' => pSQL(Configuration::get('SMAILY_PASSWORD')),
            'smaily_cart_autoresponder' => $cart_autoresponder_for_template,
            'smaily_abandoned_cart_time' => pSQL(Configuration::get('SMAILY_ABANDONED_CART_TIME')),
            'smaily_syncronize_additional' => $sync_array,
            'smaily_cart_syncronize_additional' => $cart_sync_array,
            'token' => Tools::getAdminTokenLite('AdminSmailyforprestashopAjax'),
            'smaily_rssfeed_url' => $this->generateCronUrlFromSettings(),
            'smaily_customer_cron_url' => Context::getContext()->link->getModuleLink(
                'smailyforprestashop',
                'SmailyCustomerCron'
            ),
            'smaily_cart_cron_url' => Context::getContext()->link->getModuleLink(
                'smailyforprestashop',
                'SmailyCartCron'
            ),
            'smaily_rss_available_category_ids' => $this->recursivelyNormalizeCategoriesForTemplate($categories),
            'smaily_rss_selected_category_id' => pSQL(Configuration::get('SMAILY_RSS_CATEGORY_ID')),
            'smaily_rss_limit' => pSQL(Configuration::get('SMAILY_RSS_LIMIT')),
            'smaily_rss_order_by' => pSQL(Configuration::get('SMAILY_RSS_ORDER_BY')),
            'smaily_rss_order_way' => pSQL(Configuration::get('SMAILY_RSS_ORDER_WAY')),
            )
        );
        // Display settings form.
        return $output .= $this->display(__FILE__, 'views/templates/admin/smaily_configure.tpl');
    }

    /**
     * Recursively go through categories in array and normalize for template.
     *
     * @param array $categories Enabled categories in Prestashop catalog.
     *
     * @return array Categories in format: array(category id => category name).
     */
    private function recursivelyNormalizeCategoriesForTemplate($categories)
    {
        $normalized = array();
        foreach ( $categories as $category ) {
            $normalized[$category['id_category']] = $category['name'];
            if (isset($category['children']) && is_array($category['children'])) {
                $normalized += $this->recursivelyNormalizeCategoriesForTemplate($category['children']);
            }
        }
        return $normalized;
    }

    /**
     * Make CRON URL with query parameters.
     *
     * @return string $url
     * e.g example.com/en/module/smailyforprestashop/SmailyRssFeed?limit=50&order_by=date_upd&order_way=desc&id_category=2
     */
    private function generateCronUrlFromSettings()
    {
        $query_arguments = array(
            'limit' => pSQL(Configuration::get('SMAILY_RSS_LIMIT')),
            'order_by' => pSQL(Configuration::get('SMAILY_RSS_ORDER_BY')),
            'order_way' => pSQL(Configuration::get('SMAILY_RSS_ORDER_WAY')),
        );
        if (pSQL(Configuration::get('SMAILY_RSS_CATEGORY_ID')) !== 'all_products') {
            $query_arguments['id_category'] = pSQL(Configuration::get('SMAILY_RSS_CATEGORY_ID'));
        }

        $url = Context::getContext()->link->getModuleLink(
            'smailyforprestashop',
            'SmailyRssFeed',
            $query_arguments
        );
        return $url;
    }

    // Display Block Newsletter in footer.
    public function hookDisplayFooterBefore($params)
    {
        // Add subdomain to template.
        $this->context->smarty->assign(array(
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
        ));
        return $this->display(__FILE__, 'smaily_blocknewsletter.tpl');
    }

    // Display Block Newsletter in left column.
    public function hookDisplayLeftColumn($params)
    {
        // Add subdomain to template.
        $this->context->smarty->assign(array(
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
        ));
        return $this->display(__FILE__, 'smaily_blocknewsletter_column.tpl');
    }

    // Display Block Newsletter in right column.
    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    // Add JQuerry and module javascript.
    public function hookDisplayBackOfficeHeader()
    {
        // Add module javascript.
        if (Tools::getValue('configure') === $this->name) {
            // Add JQuerry before module javascript.
            $this->context->controller->addJquery();
            $this->context->controller->addJS(array($this->_path.'views/js/smaily_module.js'));
            // Add variables for js.
            Media::addJsDef(
                array(
                    'controller_url' => $this->context->link->getAdminLink($this->controllerAdmin),
                    'smaiy_rss_url' => Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyRssFeed'),
                    'smailymessages' => array(
                        'no_autoresponders' => $this->l('No autoresponders created in Smaily!'),
                        'no_connection' => $this->l('There seems to be some problem with connecting to Smaily!'),
                        'credentials_validated' => $this->l('Smaily credentials validated!')
                    )
                )
            );
        }
    }

    /**
     * Log API response to text-file.
     *
     * @param string $filename  Name of the file created.
     * @param string $msg       Text response from api.
     * @return void
     */
    public function logToFile($filename, $response)
    {
        $logger = new FileLogger(1);
        $logger->setFilename(_PS_MODULE_DIR_. $this->name ."/" . $filename);
        $logger->logInfo('Response from API - ' . $response);
    }

    /**
     * Makes API call to Smaily.
     *
     * @param string $endpoint  Endpoint of smaily API without .php
     * @param array $data       Data to be sent to API.
     * @param string $method    'GET' or 'POST' method.
     * @return array $response  Response from smaily api.
     */
    public function callApi($endpoint, array $data, $method = 'GET')
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
}
