<?php
/*
 * 2024 Smaily
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
 * @copyright 2024 Smaily
 * @license   GPL3
 */
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmailyForPrestaShop extends Module
{
    private const CONFIGURATION_KEYS = [
        // Account
        'SMAILY_SUBDOMAIN',
        'SMAILY_USERNAME',
        'SMAILY_PASSWORD',
        // Customer Sync
        'SMAILY_ENABLE_CUSTOMER_SYNC',
        'SMAILY_CUSTOMER_CRON_TOKEN',
        'SMAILY_SYNCRONIZE_ADDITIONAL',
        'SMAILY_OPTIN_ENABLED',
        'SMAILY_OPTIN_AUTORESPONDER',
        // Abandoned Cart
        'SMAILY_ENABLE_ABANDONED_CART',
        'SMAILY_CART_CRON_TOKEN',
        'SMAILY_CART_AUTORESPONDER',
        'SMAILY_ABANDONED_CART_TIME',
        'SMAILY_CART_SYNCRONIZE_ADDITIONAL',
        // RSS
        'SMAILY_RSS_CATEGORY_ID',
        'SMAILY_RSS_LIMIT',
        'SMAILY_RSS_SORT_BY',
        'SMAILY_RSS_SORT_ORDER',
    ];

    public function __construct()
    {
        $this->name = 'smailyforprestashop';
        $this->tab = 'advertising_marketing';
        $this->module_key = 'bcea90ce4da2594c0d0179852db9a1e3';
        $this->version = '2.0.0';
        $this->author = 'Smaily';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => '8.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Smaily for PrestaShop', [], 'Modules.Smailyforprestashop.Admin');
        $this->description = $this->trans('Smaily email marketing and automation module for PrestaShop.', [], 'Modules.Smailyforprestashop.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Smailyforprestashop.Admin');
    }

    public function install(): bool
    {
        // Check if multistore is enabled
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()
            || !$this->addDefaultConfiguration()
            || !$this->createTables()
            || !$this->registerHooks()
        ) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        if (!parent::uninstall()
            || !$this->removeConfiguration()
            || !$this->dropTables()
        ) {
            return false;
        }

        return true;
    }

    public function getContent(): void
    {
        $route = $this->get('router')->generate('smailyforprestashop_module_configuration');
        Tools::redirectAdmin($route);
    }

    private function createTables(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'smaily_cart (
            `id_smaily_cart` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `id_customer` INT UNSIGNED NULL ,
            `id_cart` INT UNSIGNED NULL ,
            `date_sent` DATETIME NOT NULL) ENGINE=' . _MYSQL_ENGINE_;

        return DB::getInstance()->execute($sql);
    }

    private function dropTables(): bool
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'smaily_cart');
    }

    private function registerHooks(): bool
    {
        if (
            !$this->registerHook('actionCustomerAccountAdd')
        ) {
            return false;
        }

        return true;

        // Check that the module can be attached to the header hook.
        // || !$this->registerHook('backOfficeHeader')
        // || !$this->registerHook('footerBefore')
        // || !$this->registerHook('leftColumn')
        // || !$this->registerHook('rightColumn')
        // Add Newsletter subscription form.
        // User has option to trigger opt-in when customer joins store & newsletter through sign-up.
        // ||
    }

    private function addDefaultConfiguration(): bool
    {
        $defaults = [
            // Account
            'SMAILY_SUBDOMAIN' => '',
            'SMAILY_USERNAME' => '',
            'SMAILY_PASSWORD' => '',
            // Customer Sync
            'SMAILY_ENABLE_CUSTOMER_SYNC' => false,
            'SMAILY_CUSTOMER_CRON_TOKEN' => bin2hex(random_bytes(6)),
            'SMAILY_SYNCRONIZE_ADDITIONAL' => serialize([]),
            'SMAILY_OPTIN_ENABLED' => false,
            'SMAILY_OPTIN_AUTORESPONDER' => '',
            // Abandoned Cart
            'SMAILY_ENABLE_ABANDONED_CART' => 0,
            'SMAILY_CART_CRON_TOKEN' => bin2hex(random_bytes(6)),
            'SMAILY_CART_AUTORESPONDER' => '',
            'SMAILY_ABANDONED_CART_TIME' => 15,
            'SMAILY_CART_SYNCRONIZE_ADDITIONAL' => serialize([]),
            // RSS
            'SMAILY_RSS_CATEGORY_ID' => '',
            'SMAILY_RSS_LIMIT' => 50,
            'SMAILY_RSS_SORT_BY' => 'date_upd',
            'SMAILY_RSS_SORT_ORDER' => 'desc',
        ];

        foreach (self::CONFIGURATION_KEYS as $key) {
            if (!Configuration::updateValue($key, $defaults[$key])) {
                return false;
            }
        }

        return true;
    }

    private function removeConfiguration(): bool
    {
        foreach (self::CONFIGURATION_KEYS as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    // // Display Block Newsletter in footer.
    // public function hookDisplayFooterBefore($params)
    // {
    //     // Add subdomain to template.
    //     $this->context->smarty->assign([
    //         'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
    //     ]);

    //     return $this->display(__FILE__, 'smaily_blocknewsletter.tpl');
    // }

    // // Display Block Newsletter in left column.
    // public function hookDisplayLeftColumn($params)
    // {
    //     // Add subdomain to template.
    //     $this->context->smarty->assign([
    //         'smaily_subdomain' => pSQL(Configuration::get('SMAILY_SUBDOMAIN')),
    //     ]);

    //     return $this->display(__FILE__, 'smaily_blocknewsletter_column.tpl');
    // }

    // // Display Block Newsletter in right column.
    // public function hookDisplayRightColumn($params)
    // {
    //     return $this->hookDisplayLeftColumn($params);
    // }

    // // Add JQuerry and module javascript.
    // public function hookDisplayBackOfficeHeader()
    // {
    //     // Add module javascript.
    //     if (Tools::getValue('configure') === $this->name) {
    //         // Add JQuerry before module javascript.
    //         $this->context->controller->addJquery();
    //         $this->context->controller->addJS([$this->_path . 'views/js/smaily_module.js']);
    //         // Add variables for js.
    //         $rss_url = Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyRssFeed');
    //         Media::addJsDef(
    //             [
    //                 'controller_url' => $this->context->link->getAdminLink($this->controllerAdmin),
    //                 'smaily_rss_url' => $rss_url,
    //                 'smailymessages' => [
    //                     'no_autoresponders' => $this->trans('No autoresponders created in Smaily!'),
    //                     'no_connection' => $this->trans('There seems to be some problem with connecting to Smaily!'),
    //                     'credentials_validated' => $this->trans('Smaily credentials validated!'),
    //                 ],
    //             ]
    //         );
    //     }
    // }

    /**
     * Trigger Smaily Opt-in if customer joins with newsletter subscription.
     *
     * @param array $params array of parameters being passed to the hook function
     *
     * @return bool success of the operation
     */
    public function hookActionCustomerAccountAdd($params)
    {
        if (empty($params['newCustomer'])) {
            return false;
        }

        $customer = $params['newCustomer'];

        $controller = $this->get('prestashop.module.smailyforprestashop.controller.opt_in_controller');

        return $controller->optInCustomer($customer);
    }

    // public function installTab($parent_class, $class_name, $name): bool
    // {
    //     $tab = new Tab();
    //     $tab->id_parent = (int) Tab::getIdFromClassName($parent_class);
    //     $tab->name = [];
    //     foreach (Language::getLanguages(true) as $lang) {
    //         $tab->name[$lang['id_lang']] = $name;
    //     }
    //     $tab->class_name = $class_name;
    //     $tab->module = $this->name;
    //     $tab->active = 0;

    //     return $tab->add();
    // }

    // public function uninstallTab($class_name): bool
    // {
    //     // Retrieve Tab ID
    //     $id_tab = (int) Tab::getIdFromClassName($class_name);
    //     // Load tab
    //     $tab = new Tab((int) $id_tab);

    //     // Delete it
    //     return $tab->delete();
    // }
}
