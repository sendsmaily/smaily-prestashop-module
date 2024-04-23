<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Logger;
use PrestaShop\Module\SmailyForPrestaShop\Model\AbandonedCart;
use PrestaShop\Module\SmailyForPrestaShop\Model\AbandonedCartCollection;
use PrestaShop\Module\SmailyForPrestaShop\Model\SentCart;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

class AbandonedCartController
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Api;
     */
    private $api;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;

        $subdomain = $configuration->get('SMAILY_SUBDOMAIN');
        $username = $configuration->get('SMAILY_USERNAME');
        $password = $configuration->get('SMAILY_PASSWORD');

        if (!empty($subdomain) && !empty($username) && !empty($password)) {
            $this->api = new Api($subdomain, $username, $password);
        }
    }

    public function sync(string $token): bool
    {
        if ($this->configuration->get('SMAILY_CART_CRON_TOKEN') !== $token) {
            echo 'Access denied!';

            return false;
        }

        if (!$this->configuration->getBoolean('SMAILY_ENABLE_ABANDONED_CART')) {
            echo 'Abandoned cart disabled!';

            return false;
        }

        if (empty($this->api)) {
            echo 'Account setup not finished!';

            return false;
        }

        $count = 0;
        $failCount = 0;
        $success = true;

        $abandonedCartsCollection = new AbandonedCartCollection();
        foreach ($abandonedCartsCollection->carts as $cart) {
            // Notice!
            // When running cron job in the same browser the session is shared
            // and the abandoned cart `date_upd` time is renewed on each request.
            // This doesn't allow the check to pass. I recommend running in the cron job
            // in private window to avoid this.
            if (!$this->isDelayTimePassed($cart)) {
                break;
            }

            if (!$this->send($cart)) {
                ++$failCount;
                $success = false;
            }

            ++$count;
        }

        echo sprintf("%s abandoned cart email(s) sent!\r\n", $count);
        if (!$success) {
            echo sprintf("%s failed cart(s), check logs!\r\n", $failCount);
        }

        return $success;
    }

    private function isDelayTimePassed(AbandonedCart $cart): bool
    {
        $cart_updated_time = strtotime($cart->dateUpdated);
        $syncInterval = $this->configuration->get('SMAILY_ABANDONED_CART_TIME');

        $reminder_time = strtotime('+' . $syncInterval . ' minutes', $cart_updated_time);
        $current_time = strtotime(date('Y-m-d H:i') . ':00');
        // Don't continue if cart delay time has not passed.
        if ($current_time < $reminder_time) {
            return false;
        }

        return true;
    }

    /**
     * Send abandoned cart emails to customers.
     *
     * @return void
     */
    private function send(AbandonedCart $cart): bool
    {
        $response = $this->api->triggerAutomation(
            $this->configuration->get('SMAILY_CART_AUTORESPONDER'),
            [$this->generatePayload($cart)],
        );

        if ($response->getStatusCode() !== 200) {
            Logger::logErrorWithFormatting(
                'Failed sending out abandoned cart email for email: %s, cart_id: %s. ' .
                'Smaily response HTTP response code: %s.',
                $cart->email,
                $cart->cartID,
                $response->getStatusCode()
            );

            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!isset($body['code']) || $body['code'] !== 101) {
            Logger::logErrorWithFormatting(
                'Failed sending out abandoned cart email for email: %s, cart_id: %s. ' .
                'Smaily response code: %s, message: %s.',
                $cart->email,
                $cart->cartID,
                $body['code'],
                $body['message']
            );

            return false;
        }

        $sentCart = new SentCart();
        $sentCart->id_customer = $cart->customerID;
        $sentCart->id_cart = $cart->cartID;
        $sentCart->save();

        return true;
    }

    private function generatePayload(AbandonedCart $cart): array
    {
        $payload = [
            'email' => $cart->email,
        ];

        $syncAdditional = unserialize($this->configuration->get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'));
        if (in_array('first_name', $syncAdditional)) {
            $payload['first_name'] = $cart->firstName;
        }

        if (in_array('last_name', $syncAdditional)) {
            $payload['last_name'] = $cart->lastName;
        }

        // Populate abandoned cart with empty values for legacy api.
        $fields_available = [
            'name',
            'description',
            'sku',
            'price',
            'quantity',
            'base_price',
        ];
        foreach ($fields_available as $field) {
            for ($i = 1; $i <= 10; ++$i) {
                $payload['product_' . $field . '_' . $i] = '';
            }
        }

        $selected_fields = array_intersect($fields_available, array_keys(array_filter($syncAdditional)));

        // Collect products of abandoned cart.
        $count = 1;
        $currency = \Context::getContext()->currency->iso_code;
        foreach ($cart->products as $product) {
            // Get only 10 products.
            if ($count > 10) {
                $payload['over_10_products'] = 'true';
                break;
            }
            // Standardize template parameters across integrations.
            foreach ($selected_fields as $sync_field) {
                switch ($sync_field) {
                    case 'base_price':
                        $payload['product_base_price_' . $count] = \Context::getContext()->currentLocale->formatPrice(
                            $product['price_without_reduction'],
                            $currency
                        );
                        break;
                    case 'price':
                        $payload['product_price_' . $count] = \Context::getContext()->currentLocale->formatPrice(
                            $product['price_with_reduction'],
                            $currency
                        );
                        break;
                    case 'sku':
                        $payload['product_sku_' . $count] = $product['reference'];
                        break;
                    case 'description':
                        $payload['product_description_' . $count] = htmlspecialchars(
                            $product['description_short']
                        );
                        break;
                    default:
                        $payload['product_' . $sync_field . '_' . $count] = $product[$sync_field];
                        break;
                }
            }
            ++$count;
        }

        return $payload;
    }
}