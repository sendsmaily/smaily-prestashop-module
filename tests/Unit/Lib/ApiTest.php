<?php
declare(strict_types=1);

namespace Tests\Unit\Lib;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * The API client turns each operation into one request to the organization's Smaily API host,
 * authenticated as the API user.
 */
final class ApiTest extends TestCase
{
    private const HOST = 'https://acme.sendsmaily.net/';

    /** @var array<int, array{method: string, url: string, headers: array<string, string[]>, body: string}> */
    private array $requests = [];

    private Api $api;

    protected function setUp(): void
    {
        $transport = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->requests[] = [
                'method' => $method,
                'url' => $url,
                'headers' => $options['normalized_headers'],
                'body' => (string) ($options['body'] ?? ''),
            ];

            return new MockResponse('{"code": 101}');
        });

        $this->api = new Api('acme', 'user', 'secret', $transport);
    }

    public function testListsAutorespondersUpToTheLimit(): void
    {
        $this->api->listAutoresponders(25);

        $this->assertRequest('GET', self::HOST . 'api/autoresponder.php?limit=25');
    }

    public function testListsUnsubscribersPageByPage(): void
    {
        $this->api->listUnsubscribers(50, 100);

        $this->assertRequest('GET', self::HOST . 'api/contact.php?list=2&limit=50&offset=100');
    }

    public function testCreatesSubscribersFromJson(): void
    {
        $this->api->createSubscribers([['email' => 'a@example.com']]);

        $this->assertRequest('POST', self::HOST . 'api/contact.php');
        $this->assertJsonBody([['email' => 'a@example.com']]);
    }

    public function testUpdatesASubscriberFromJson(): void
    {
        $this->api->updateSubscriber(['email' => 'a@example.com', 'is_unsubscribed' => 1]);

        $this->assertRequest('POST', self::HOST . 'api/contact.php');
        $this->assertJsonBody(['email' => 'a@example.com', 'is_unsubscribed' => 1]);
    }

    public function testOptsInSubscribers(): void
    {
        $this->api->optInSubscribers([['email' => 'a@example.com']]);

        $this->assertRequest('POST', self::HOST . 'api/autoresponder.php');
        $this->assertJsonBody(['addresses' => [['email' => 'a@example.com']]]);
    }

    public function testTriggersAnAutomationForSubscribers(): void
    {
        $this->api->triggerAutomation('42', [['email' => 'a@example.com']]);

        $this->assertRequest('POST', self::HOST . 'api/autoresponder.php');
        $this->assertJsonBody(['autoresponder' => '42', 'addresses' => [['email' => 'a@example.com']]]);
    }

    public function testAuthenticatesEveryRequestAsTheApiUser(): void
    {
        $this->api->listAutoresponders();

        self::assertSame(['Authorization: Basic ' . base64_encode('user:secret')], $this->requests[0]['headers']['authorization']);
    }

    public function testHandsBackTheStatusAndBodyOfAnErrorResponse(): void
    {
        $transport = new MockHttpClient(new MockResponse('{"code": 401, "message": "Unauthorized"}', ['http_code' => 401]));
        $api = new Api('acme', 'user', 'wrong', $transport);

        $response = $api->listAutoresponders();

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(['code' => 401, 'message' => 'Unauthorized'], json_decode($response->getContent(false), true));
    }

    private function assertRequest(string $method, string $url): void
    {
        self::assertCount(1, $this->requests, 'exactly one request is sent');
        self::assertSame($method . ' ' . $url, $this->requests[0]['method'] . ' ' . $this->requests[0]['url']);
    }

    private function assertJsonBody(array $expected): void
    {
        self::assertSame(['Content-Type: application/json'], $this->requests[0]['headers']['content-type']);
        self::assertSame($expected, json_decode($this->requests[0]['body'], true));
    }
}
