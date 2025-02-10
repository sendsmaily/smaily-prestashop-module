<?php
/**
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

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Tools;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Logger;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

class OptInController
{
    /**
     * Smaily modules configuration.
     * 
     * @var ConfigurationInterface;
     */
    private $_configuration;

    /**
     * Smaily API client.
     *
     * @var Api
     */
    private $_api;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->_configuration = $configuration;

        $subdomain = $this->_configuration->get('SMAILY_SUBDOMAIN');
        $username = $this->_configuration->get('SMAILY_USERNAME');
        $password = $this->_configuration->get('SMAILY_PASSWORD');

        if (!empty($subdomain) && !empty($username) && !empty($password)) {
            $this->_api = new Api($subdomain, $username, $password);
        }
    }

    /**
     * Trigger opt-in flow during customer account creation.
     *
     * @param \Customer $customer
     * @return bool
     */
    public function optInCustomer(\Customer $customer): bool
    {
        if ($customer->newsletter !== '1') {
            return false;
        }

        if (!$this->_configuration->getBoolean('SMAILY_OPTIN_ENABLED')) {
            return false;
        }

        if (empty($this->_api)) {
            return false;
        }

        return $this->_optin($customer->email);
    }

    /**
     * Trigger opt-in flow when subscribing through subscribe form.
     *
     * @param string $email Subscribers email.
     *
     * @return bool
     */
    public function optInSubscriber(string $email): bool
    {
        if (!$this->_configuration->getBoolean('SMAILY_OPTIN_ENABLED')) {
            return false;
        }

        if (empty($this->_api)) {
            return false;
        }

        return $this->_optin($email);
    }

    /**
     * Trigger a a opt-in flow or automation based on module configuration.
     *
     * @param string $email Subscribers email
     *
     * @return bool
     */
    private function _optin(string $email)
    {
        $autoresponder = $this->_configuration->get('SMAILY_OPTIN_AUTORESPONDER');
        $response = empty($autoresponder) ?
        $this->_api->optInSubscribers(
            [
                [
                    'email' => $email,
                    'store' => Tools::getShopDomain()
                ]
            ]
        ) : 
        $this->_api->triggerAutomation(
            $autoresponder,
            [
                [
                    'email' => $email,
                    'store' => Tools::getShopDomain()
                ]
            ]
        );

        if ($response->getStatusCode() !== 200) {
            Logger::logErrorWithFormatting(
                'Failed to register a subscriber with an email: %s, ' .
                'Smaily response HTTP response code: %s.',
                $email,
                $response->getStatusCode()
            );

            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!isset($body['code']) || $body['code'] !== 101) {
            Logger::logErrorWithFormatting(
                'Failed to register a subscriber with an email: %s, ' .
                'Smaily response code: %s, message: %s.',
                $email,
                isset($body['code']) ? $body['code'] : '<none>',
                isset($body['message']) ? $body['message'] : '<none>'
            );

            return false;
        }

        return true;
    }
}
