<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Lib;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Api
{
    /**
     * @var \GuzzleHttp\Client
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
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
        ]);
    }

    public function triggerAutomation(string $autoresponder, array $addresses): ResponseInterface
    {
        return $this->client->post('/api/autoresponder.php', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => [
                'addresses' => json_encode($addresses),
            ],
        ]);
    }
}
