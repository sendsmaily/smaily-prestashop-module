<?php
/**
 * Runs inside a shop container: sends one request from the module's API client through the Symfony
 * HTTP client that this PrestaShop ships, over a mock transport. A package PrestaShop stopped
 * bundling, or a request option it no longer accepts, fails here instead of in a customer's shop.
 */
declare(strict_types=1);

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

define('_PS_VERSION_', '0.0.0');
require getenv('PS_VENDOR_AUTOLOAD') ?: '/var/www/html/vendor/autoload.php';
require getenv('MODULE_API') ?: '/var/www/html/modules/smailyforprestashop/lib/Api.php';

$sent = 'nothing';
$transport = new MockHttpClient(function (string $method, string $url, array $options) use (&$sent): MockResponse {
    $sent = $method . ' ' . $url . ' ' . ($options['normalized_headers']['authorization'][0] ?? 'without authorization');

    return new MockResponse('{"code": 101}');
});

$response = (new Api('acme', 'user', 'secret', $transport))->createSubscribers([['email' => 'a@example.com']]);
$expected = 'POST https://acme.sendsmaily.net/api/contact.php Authorization: Basic ' . base64_encode('user:secret');

if ($sent !== $expected || $response->getStatusCode() !== 200 || json_decode($response->getContent(false), true) !== ['code' => 101]) {
    fwrite(STDERR, "API client smoke check failed, sent: $sent\n");
    exit(1);
}

echo "the API client sends requests through this PrestaShop's HTTP client\n";
