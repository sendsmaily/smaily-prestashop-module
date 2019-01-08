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
        $this->version = '1.1.0';
        $this->author = 'Smaily';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7.0',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;

        parent::__construct();

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
            !Configuration::updateValue('SMAILY_CRON_TOKEN', '') ||
            !Configuration::updateValue('SMAILY_SUBDOMAIN', '') ||
            !Configuration::updateValue('SMAILY_USERNAME', '') ||
            !Configuration::updateValue('SMAILY_PASSWORD', '') ||
            !Configuration::updateValue('SMAILY_API_KEY', '') ||
            !Configuration::updateValue('SMAILY_AUTORESPONDER', '') ||
            !Configuration::updateValue('SMAILY_CART_AUTORESPONDER', '') ||
            !Configuration::updateValue('SMAILY_ABANDONED_CART_TIME', '') ||
            !Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize(array())) ||
            !Configuration::updateValue('SMAILY_CART_SYNCRONIZE_ADDITIONAL', serialize(array())) ||
            // Add tab to sidebar
            !$this->installTab('AdminAdmin', 'AdminSmailyforprestashopAjax', 'Smaily for PrestaShop') ||
            // Add Newsletter subscription form.
            !$this->registerHook('footerBefore')
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
        !Configuration::deleteByName('SMAILY_CRON_TOKEN') ||
        !Configuration::deleteByName('SMAILY_SUBDOMAIN') ||
        !Configuration::deleteByName('SMAILY_USERNAME') ||
        !Configuration::deleteByName('SMAILY_PASSWORD') ||
        !Configuration::deleteByName('SMAILY_API_KEY') ||
        !Configuration::deleteByName('SMAILY_AUTORESPONDER') ||
        !Configuration::deleteByName('SMAILY_CART_AUTORESPONDER') ||
        !Configuration::deleteByName('SMAILY_ABANDONED_CART_TIME') ||
        !Configuration::deleteByName('SMAILY_SYNCRONIZE_ADDITIONAL') ||
        !Configuration::deleteByName('SMAILY_CART_SYNCRONIZE_ADDITIONAL') ||
        // Remove sideTab of smaily module.
        !$this->uninstallTab('AdminSmailyforprestashopAjax')
        ) {
            return false;
        }
        Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'smaily_cart');
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

        if (Tools::isSubmit('smaily_submit_configuration')) {
            // Enable Cron.
            $enable_cron = pSQL(Tools::getValue('SMAILY_ENABLE_CRON'));
            // Cron token.
            $cron_token = pSQL(Tools::getValue('SMAILY_CRON_TOKEN'));
            $cron_token = trim(Tools::stripslashes($cron_token));
            // Subdomain.
            $subdomain = pSQL(Tools::getValue('SMAILY_SUBDOMAIN'));
            $subdomain = trim(Tools::stripslashes($subdomain));
            // Username
            $username = pSQL(Tools::getValue('SMAILY_USERNAME'));
            $username = trim(Tools::stripslashes($username));
            // Password.
            $password = pSQL(Tools::getValue('SMAILY_PASSWORD'));
            $password = trim(Tools::stripslashes($password));
            // Api key.
            $api_key = pSQL(Tools::getValue('SMAILY_API_KEY'));
            $api_key = trim(Tools::stripslashes($api_key));
            // Autoresponder.
            $autoresponder = pSQL((Tools::getValue('SMAILY_AUTORESPONDER')));
            $autoresponder = str_replace('\"', '"', $autoresponder);
            // Get autoresponder array from json string.
            $autoresponder = Tools::jsonDecode($autoresponder);
            // Clean autoresponder for inserting to database.
            $escaped_autoresponder = array();
            if (!empty($autoresponder)) {
                foreach ($autoresponder as $key => $value) {
                    $escaped_autoresponder[ pSQL($key)] = pSQL($value);
                }
            }
            // Syncronize additional.
            $syncronize_additional = Tools::getValue('SMAILY_SYNCRONIZE_ADDITIONAL');
            $escaped_sync_additional = array();
            if (!empty($syncronize_additional)) {
                foreach ($syncronize_additional as $key => $value) {
                    $escaped_sync_additional[] = pSQL($value);
                }
            }
            if (!$subdomain ||
                empty($subdomain) ||
                !$username ||
                empty($username) ||
                !$password ||
                empty($password) ||
                !$api_key ||
                empty($api_key) ||
                !$autoresponder ||
                empty($autoresponder)
            ) {
                // Display error message.
                $output .= $this->displayError($this->l('Please fill out required fields.'));
            } else {
                // Update settings.
                Configuration::updateValue('SMAILY_ENABLE_CRON', $enable_cron);
                Configuration::updateValue('SMAILY_CRON_TOKEN', $cron_token);
                Configuration::updateValue('SMAILY_SUBDOMAIN', $subdomain);
                Configuration::updateValue('SMAILY_USERNAME', $username);
                Configuration::updateValue('SMAILY_PASSWORD', $password);
                Configuration::updateValue('SMAILY_API_KEY', $api_key);
                Configuration::updateValue('SMAILY_AUTORESPONDER', serialize($escaped_autoresponder));
                Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize($escaped_sync_additional));
                // Display success message.
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        if (Tools::isSubmit('smaily_submit_abandoned_cart')) {
            // Enable Abandoned Cart.
            $enable_abandoned_cart = pSQL(Tools::getValue('SMAILY_ENABLE_ABANDONED_CART'));
            // Abandoned cart delay time
            $abandoned_cart_time = pSQL(Tools::getValue('SMAILY_ABANDONED_CART_TIME'));
            $abandoned_cart_time = (int) trim(Tools::stripslashes($abandoned_cart_time));
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
            if ($abandoned_cart_time < 1) {
                // Display error message.
                $output .= $this->displayError($this->l('Abandoned cart delay has to be number value over 0.'));
            } else {
                Configuration::updateValue('SMAILY_ENABLE_ABANDONED_CART', $enable_abandoned_cart);
                Configuration::updateValue('SMAILY_CART_AUTORESPONDER', serialize($escaped_cart_autoresponder));
                Configuration::updateValue('SMAILY_ABANDONED_CART_TIME', $abandoned_cart_time);
                Configuration::updateValue(
                    'SMAILY_CART_SYNCRONIZE_ADDITIONAL',
                    serialize($cart_escaped_sync_additional)
                );
                // Display success message.
                $output .= $this->displayConfirmation($this->l('Abandoned cart settings updated'));
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
        // Get autoresponder values for template.
        $autoresponder_for_template = pSQL((Configuration::get('SMAILY_AUTORESPONDER')));
        $autoresponder_for_template = str_replace('\"', '"', $autoresponder_for_template);
        $autoresponder_for_template = unserialize($autoresponder_for_template);
        // Get abandoned cart autoresponder values for template.
        $cart_autoresponder_for_template = pSQL((Configuration::get('SMAILY_CART_AUTORESPONDER')));
        $cart_autoresponder_for_template = str_replace('\"', '"', $cart_autoresponder_for_template);
        $cart_autoresponder_for_template = unserialize($cart_autoresponder_for_template);
        // Assign variables to template if available.
        $this->context->smarty->assign(array(
            'smaily_enable_cron' =>  pSQL(Configuration::get('SMAILY_ENABLE_CRON')),
            'smaily_enable_abandoned_cart' => pSQL(Configuration::get('SMAILY_ENABLE_ABANDONED_CART')),
            'smaily_cron_token' =>  pSQL(Configuration::get('SMAILY_CRON_TOKEN')),
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
            'smaily_username' => pSQL(Configuration::get('SMAILY_USERNAME')),
            'smaily_password' => pSQL(Configuration::get('SMAILY_PASSWORD')),
            'smaily_api_key' => pSQL(Configuration::get('SMAILY_API_KEY')),
            'smaily_autoresponder' => $autoresponder_for_template,
            'smaily_cart_autoresponder' => $cart_autoresponder_for_template,
            'smaily_abandoned_cart_time' => pSQL(Configuration::get('SMAILY_ABANDONED_CART_TIME')),
            'smaily_syncronize_additional' => $sync_array,
            'smaily_cart_syncronize_additional' => $cart_sync_array,
            'token' => Tools::getAdminTokenLite('AdminSmailyforprestashopAjax'),
            'smaily_rssfeed_url' => Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyRssFeed'),
            'smaily_cron_url' => Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyCron'),
          ));
        // Display settings form.
        return $output .= $this->display(__FILE__, 'views/templates/admin/smaily_configure.tpl');
    }

    // Display Block Newsletter
    public function hookDisplayFooterBefore($params)
    {
        // Get autoresponder ID from settings.
        $autoresponder = unserialize(Configuration::get('SMAILY_AUTORESPONDER'));
        $autoresponder_id = pSQL($autoresponder['id']);
        // Add values to template.
        $this->context->smarty->assign(array(
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
            'smaily_api_key' => pSQL(Configuration::get('SMAILY_API_KEY')),
            'smaily_autoresponder' => $autoresponder_id
            ));
          return $this->display(__FILE__, 'smaily_blocknewsletter.tpl');
    }
    // Add JQuerry and module javascript.
    public function hookDisplayBackOfficeHeader()
    {
        // Add module javascript.
        if (Tools::getValue('configure') === $this->name) {
            // Add JQuerry before module javascript.
            $this->context->controller->addJquery();
            $this->context->controller->addJS(array($this->_path.'views/js/smaily_module.js'));
        }
    }
}
