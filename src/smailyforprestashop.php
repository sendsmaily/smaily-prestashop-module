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

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\SmailyForPrestaShop\Controller\OptInController;
use PrestaShop\Module\SmailyForPrestaShop\Install\Installer;

class SmailyForPrestaShop extends Module
{
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
        if (!parent::install()) {
            return false;
        }

        // Check if multistore is enabled
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $installer = new Installer();

        return $installer->install($this);
    }

    public function uninstall(): bool
    {
        $installer = new Installer();

        return $installer->uninstall() && parent::uninstall();
    }

    public function getContent(): void
    {
        $route = $this->get('router')->generate('smailyforprestashop_module_configuration');
        Tools::redirectAdmin($route);
    }

    public function hookActionNewsletterRegistrationAfter($params)
    {
        if (isset($params['email'], $params['module']) && $params['module'] === 'ps_emailsubscription') {
            /** @var OptInController */
            $controller = $this->get('prestashop.module.smailyforprestashop.controller.opt_in_controller');
            $controller->optInSubscriber($params['email']);
        }
    }

    /**
     * Trigger Smaily Opt-in if customer joins with newsletter subscription.
     *
     * @param array $params array of parameters being passed to the hook function
     *
     * @return bool success of the operation
     */
    public function hookActionCustomerAccountAdd($params): bool
    {
        if (empty($params['newCustomer'])) {
            return false;
        }

        $customer = $params['newCustomer'];

        /** @var OptInController */
        $controller = $this->get('prestashop.module.smailyforprestashop.controller.opt_in_controller');

        return $controller->optInCustomer($customer);
    }
}
