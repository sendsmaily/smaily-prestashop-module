<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestashop\Lib;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Api
{
    /**
     * @var string
     */
    private $subdomain;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct(string $subdomain, string $username, string $password)
    {
        $this->subdomain = $subdomain;
        $this->username = $username;
        $this->password = $password;

        $this->client = new Client([
            'base_uri' => 'https://' . $subdomain . '.sendsmaily.net/',
            'http_errors' => false,
        ]);
    }

    public function listAutoresponders(int $limit = 100): ResponseInterface
    {
        return $this->client->request('GET', 'api/autoresponder.php', [
            'auth' => [$this->username, $this->password],
            'query' => [
                'limit' => $limit,
            ],
        ]);
    }
}
