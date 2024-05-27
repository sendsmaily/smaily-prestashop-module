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

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Logger;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

class OptInController
{
    /**
     * @var ConfigurationInterface;
     */
    private $configuration;

    /**
     * @var Api
     */
    private $api;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;

        $subdomain = $this->configuration->get('SMAILY_SUBDOMAIN');
        $username = $this->configuration->get('SMAILY_USERNAME');
        $password = $this->configuration->get('SMAILY_PASSWORD');

        if (!empty($subdomain) && !empty($username) && !empty($password)) {
            $this->api = new Api($subdomain, $username, $password);
        }
    }

    public function optInCustomer(\Customer $customer): bool
    {
        if ($customer->newsletter !== '1') {
            return false;
        }

        if (!$this->configuration->getBoolean('SMAILY_OPTIN_ENABLED')) {
            return false;
        }

        if (empty($this->api)) {
            return false;
        }

        $autoresponder = $this->configuration->get('SMAILY_OPTIN_AUTORESPONDER');
        if (empty($autoresponder)) {
            $response = $this->api->optInSubscribers([['email' => $customer->email]]);
        } else {
            $response = $this->api->triggerAutomation($autoresponder, [
                ['email' => $customer->email],
            ]);
        }

        if ($response->getStatusCode() !== 200) {
            Logger::logErrorWithFormatting(
                'Failed to opt-in customer with email: %s, ' .
                'Smaily response HTTP response code: %s.',
                $customer->email,
                $response->getStatusCode()
            );

            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!isset($body['code']) || $body['code'] !== 101) {
            Logger::logErrorWithFormatting('Failed to opt-in new customer with email: %s . ' .
            'Smaily response code: %s, message: %s.',
                $customer->email,
                isset($body['code']) ? $body['code'] : '<none>',
                isset($body['message']) ? $body['message'] : '<none>'
            );

            return false;
        }

        return true;
    }

    public function optInSubscriber(string $email): bool
    {
        if (!$this->configuration->getBoolean('SMAILY_OPTIN_ENABLED')) {
            return false;
        }

        $autoresponder = $this->configuration->get('SMAILY_OPTIN_AUTORESPONDER');
        if (empty($this->api)) {
            return false;
        }

        if (empty($autoresponder)) {
            $response = $this->api->optInSubscribers([['email' => $email]]);
        } else {
            $response = $this->api->triggerAutomation($autoresponder, [
                ['email' => $email],
            ]);
        }

        if ($response->getStatusCode() !== 200) {
            Logger::logErrorWithFormatting(
                'Failed to opt-in customer with email: %s, ' .
                'Smaily response HTTP response code: %s.',
                $email,
                $response->getStatusCode()
            );

            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!isset($body['code']) || $body['code'] !== 101) {
            Logger::logErrorWithFormatting('Failed to opt-in new customer with email: %s . ' .
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
