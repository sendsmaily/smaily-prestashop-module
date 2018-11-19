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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmailyForPrestashop extends Module
{
    public function __construct()
    {
        $this->name = 'smailyforprestashop';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'Smaily';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => '1.7.4.2'
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
            !Configuration::updateValue('SMAILY_CRON_TOKEN', '') ||
            !Configuration::updateValue('SMAILY_SUBDOMAIN', '') ||
            !Configuration::updateValue('SMAILY_USERNAME', '') ||
            !Configuration::updateValue('SMAILY_PASSWORD', '') ||
            !Configuration::updateValue('SMAILY_API_KEY', '') ||
            !Configuration::updateValue('SMAILY_AUTORESPONDER', '') ||
            !Configuration::updateValue('SMAILY_SYNCRONIZE_ADDITIONAL', serialize(array())) ||
            // Add tab to sidebar
            !$this->installTab('AdminAdmin', 'AdminSmailyforprestashopAjax', 'Smaily for PrestaShop') ||
            // Add Newsletter subscription form.
            !$this->registerHook('footerBefore')
        ) {
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
        !Configuration::deleteByName('SMAILY_CRON_TOKEN') ||
        !Configuration::deleteByName('SMAILY_SUBDOMAIN') ||
        !Configuration::deleteByName('SMAILY_USERNAME') ||
        !Configuration::deleteByName('SMAILY_PASSWORD') ||
        !Configuration::deleteByName('SMAILY_API_KEY') ||
        !Configuration::deleteByName('SMAILY_AUTORESPONDER') ||
        !Configuration::deleteByName('SMAILY_SYNCRONIZE_ADDITIONAL') ||
        // Remove sideTab of smaily module.
        !$this->uninstallTab('AdminSmailyforprestashopAjax')
        ) {
            return false;
        }
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
            $cron_token = str_replace(' ', '', Tools::stripslashes($cron_token));
            // Subdomain.
            $subdomain = pSQL(Tools::getValue('SMAILY_SUBDOMAIN'));
            $subdomain = str_replace(' ', '', Tools::stripslashes($subdomain));
            // Username
            $username = pSQL(Tools::getValue('SMAILY_USERNAME'));
            $username = str_replace(' ', '', Tools::stripslashes($username));
            // Password.
            $password = pSQL(Tools::getValue('SMAILY_PASSWORD'));
            $password = str_replace(' ', '', Tools::stripslashes($password));
            // Api key.
            $api_key = pSQL(Tools::getValue('SMAILY_API_KEY'));
            $api_key = str_replace(' ', '', Tools::stripslashes($api_key));
            // Autoresponder
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
        // Get syncronize additional values.
        if (false !== unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'))) {
            $sync_array = unserialize(Configuration::get('SMAILY_SYNCRONIZE_ADDITIONAL'));
        } else {
            $sync_array = array();
        }
        // Assign variables to template if available.
        $this->context->smarty->assign(array(
            'smaily_enable_cron' =>  pSQL(Configuration::get('SMAILY_ENABLE_CRON')),
            'smaily_cron_token' =>  pSQL(Configuration::get('SMAILY_CRON_TOKEN')),
            'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
            'smaily_username' => pSQL(Configuration::get('SMAILY_USERNAME')),
            'smaily_password' => pSQL(Configuration::get('SMAILY_PASSWORD')),
            'smaily_api_key' => pSQL(Configuration::get('SMAILY_API_KEY')),
            'smaily_autoresponder' => (pSQL(Configuration::get('SMAILY_AUTORESPONDER'))),
            'smaily_syncronize_additional' => $sync_array,
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
    // Add Css, JQuerry and module javascript.
    public function hookDisplayBackOfficeHeader()
    {
        // Add Css.
        $this->context->controller->addCSS(array($this->_path.'views/css/smaily_module.css'));
        // Add JQuerry before module javascript.
        $this->context->controller->addJquery();
        // Add module javascript.
        if (Tools::getValue('configure') === $this->name) {
            $this->context->controller->addJS(array($this->_path.'views/js/smaily_module.js'));
        }
    }
}
