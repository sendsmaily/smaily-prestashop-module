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

namespace PrestaShop\Module\SmailyForPrestaShop\Lib;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * Options every request carries: the organization's API host and the API user's credentials.
     *
     * @var array
     */
    private $options;

    /**
     * Create an API client.
     *
     * @param string $subdomain
     * @param string $username
     * @param string $password
     * @param HttpClientInterface|null $client the HTTP client to send requests through; a new Symfony HTTP client by default
     */
    public function __construct(string $subdomain, string $username, string $password, ?HttpClientInterface $client = null)
    {
        $this->client = $client ?? HttpClient::create();
        $this->options = [
            'base_uri' => 'https://' . $subdomain . '.sendsmaily.net/',
            'auth_basic' => [$username, $password],
        ];
    }

    /**
     * Returns a list of automation workflows of an organization.
     *
     * @param int $limit
     *
     * @return ResponseInterface
     */
    public function listAutoresponders(int $limit = 100): ResponseInterface
    {
        return $this->request('GET', 'api/autoresponder.php', [
            'query' => [
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Returns a list of contacts with unsubscribed status for an organization.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return ResponseInterface
     */
    public function listUnsubscribers(int $limit = 100, $offset = 0): ResponseInterface
    {
        return $this->request('GET', 'api/contact.php', [
            'query' => [
                'list' => 2,
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * Creates subscribers with requested fields. This API endpoint does not trigger automation workflows.
     *
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function createSubscribers(array $data): ResponseInterface
    {
        return $this->request('POST', 'api/contact.php', [
            'json' => $data,
        ]);
    }

    /**
     * Update subscriber information.
     *
     * @param array $data
     *
     * @return ResponseInterface
     */
    public function updateSubscriber(array $data): ResponseInterface
    {
        return $this->request('POST', 'api/contact.php', [
            'json' => $data,
        ]);
    }

    /**
     * Opt-in subscribers. This endpoint triggers “subscriber opted-in” workflow.
     *
     * @param array $addresses
     *
     * @return ResponseInterface
     */
    public function optInSubscribers(array $addresses): ResponseInterface
    {
        return $this->request('POST', 'api/autoresponder.php', [
            'json' => ['addresses' => $addresses],
        ]);
    }

    /**
     * Endpoint can be used to opt-in subscribers and target specific automation workflows with “form submitted” trigger.
     *
     * @param string $autoresponder
     * @param array $addresses
     *
     * @return ResponseInterface
     */
    public function triggerAutomation(string $autoresponder, array $addresses): ResponseInterface
    {
        return $this->request('POST', 'api/autoresponder.php', [
            'json' => [
                'autoresponder' => $autoresponder,
                'addresses' => $addresses,
            ],
        ]);
    }

    private function request(string $method, string $path, array $options): ResponseInterface
    {
        return $this->client->request($method, $path, $options + $this->options);
    }
}
