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

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Api
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(string $subdomain, string $username, string $password)
    {
        $this->client = new Client([
            'base_uri' => 'https://' . $subdomain . '.sendsmaily.net/',
            'auth' => [$username, $password],
            'http_errors' => false,
        ]);
    }

    public function listAutoresponders(int $limit = 100): ResponseInterface
    {
        return $this->client->get('api/autoresponder.php', [
            'query' => [
                'limit' => $limit,
            ],
        ]);
    }

    public function listUnsubscribers(int $limit = 100, $offset = 0): ResponseInterface
    {
        return $this->client->get('api/contact.php', [
            'query' => [
                'list' => 2,
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    public function createSubscribers(array $data): ResponseInterface
    {
        return $this->client->post('api/contact.php', [
            RequestOptions::JSON => $data,
        ]);
    }

    public function triggerAutomation(string $autoresponder, array $addresses): ResponseInterface
    {
        return $this->client->post('/api/autoresponder.php', [
            RequestOptions::JSON => [
                'autoresponder' => $autoresponder,
                'addresses' => $addresses,
            ],
        ]);
    }
}
