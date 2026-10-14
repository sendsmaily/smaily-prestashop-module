<?php
/**
 * Exercises lib/Api.php against mock.php served by the PHP built-in server on 127.0.0.1:8765.
 *
 * Usage: php -S 127.0.0.1:8765 mock.php &  then
 *        AUTOLOAD=<autoload.php that provides symfony/http-client> API_PHP=<path to lib/Api.php> php test.php
 *
 * The PrestaShop install check runs it inside the shop container, so the Symfony HTTP client under
 * test is the one that PrestaShop release ships.
 */
declare(strict_types=1);

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }
}

define('_PS_VERSION_', '0.0.0');
require getenv('AUTOLOAD');
require getenv('API_PHP');

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

$fails = 0;
function ok(bool $cond, string $msg): void
{
    global $fails;
    echo ($cond ? '  ok   ' : '  FAIL ') . $msg . "\n";
    if (!$cond) {
        ++$fails;
    }
}

// Builds the real client, then points it at the mock server with the same options.
function api(string $user, string $pass): Api
{
    $api = new Api('sub', $user, $pass);
    $p = new ReflectionProperty(Api::class, 'client');
    $p->setAccessible(true);
    ok($p->getValue($api) instanceof HttpClientInterface, 'constructor builds a Symfony HTTP client');
    $p->setValue($api, HttpClient::create(['base_uri' => 'http://127.0.0.1:8765/', 'auth_basic' => [$user, $pass]]));

    return $api;
}

$addr = [['email' => 'a@example.com', 'first_name' => 'A']];
$api = api('user', 'pass');

$r = $api->listAutoresponders();
ok($r->getStatusCode() === 200, 'listAutoresponders: status 200');
$b = json_decode($r->getContent(false), true);
ok($b['echo']['method'] === 'GET' && $b['echo']['uri'] === '/api/autoresponder.php?limit=100', 'listAutoresponders: GET /api/autoresponder.php?limit=100 (got ' . $b['echo']['uri'] . ')');
ok($b['echo']['user'] === 'user', 'listAutoresponders: basic auth sent');

$b = json_decode($api->listUnsubscribers(50, 100)->getContent(false), true);
ok($b['echo']['uri'] === '/api/contact.php?list=2&limit=50&offset=100', 'listUnsubscribers: query string (got ' . $b['echo']['uri'] . ')');

$b = json_decode($api->createSubscribers($addr)->getContent(false), true);
ok($b['echo']['method'] === 'POST' && $b['echo']['uri'] === '/api/contact.php', 'createSubscribers: POST /api/contact.php');
ok(str_starts_with((string) $b['echo']['content_type'], 'application/json'), 'createSubscribers: JSON content type (got ' . $b['echo']['content_type'] . ')');
ok(json_decode($b['echo']['body'], true) === $addr, 'createSubscribers: JSON body');

$b = json_decode($api->updateSubscriber(['email' => 'a@example.com', 'is_unsubscribed' => 1])->getContent(false), true);
ok($b['echo']['uri'] === '/api/contact.php' && json_decode($b['echo']['body'], true) === ['email' => 'a@example.com', 'is_unsubscribed' => 1], 'updateSubscriber: POST body');

$b = json_decode($api->optInSubscribers($addr)->getContent(false), true);
ok($b['echo']['uri'] === '/api/autoresponder.php' && json_decode($b['echo']['body'], true) === ['addresses' => $addr], 'optInSubscribers: POST /api/autoresponder.php with addresses');

$b = json_decode($api->triggerAutomation('42', $addr)->getContent(false), true);
ok(json_decode($b['echo']['body'], true) === ['autoresponder' => '42', 'addresses' => $addr], 'triggerAutomation: POST body with autoresponder and addresses');

// The callers read the status and the body of an error response without catching anything.
$bad = api('user', 'wrong');
$r = $bad->listAutoresponders();
ok($r->getStatusCode() === 401, 'wrong password: status 401 without exception');
ok(json_decode($r->getContent(false), true)['code'] === 401, 'wrong password: body readable without exception');
unset($r, $b, $api, $bad);
gc_collect_cycles();

echo $fails === 0 ? "ALL OK\n" : "$fails FAILED\n";
exit($fails === 0 ? 0 : 1);
