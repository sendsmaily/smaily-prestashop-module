<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\Module\SmailyForPrestaShop\Lib\Logger;
use PrestaShop\Module\SmailyForPrestaShop\Model\Subscriber;
use PrestaShop\Module\SmailyForPrestaShop\Model\SubscriberCollection;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

class CustomerSyncController
{
    /**
     * Limit unsubscribers request batch size.
     */
    private const UNSUBSCRIBERS_BATCH_LIMIT = 1000;

    /**
     * Limit subscribers query batch size.
     */
    private const SUBSCRIBERS_BATCH_LIMIT = 1000;

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
        if ($token !== $this->configuration->get('SMAILY_CUSTOMER_CRON_TOKEN')) {
            echo 'Access denied!';

            return false;
        }

        if (!$this->configuration->get('SMAILY_ENABLE_CUSTOMER_SYNC')) {
            echo 'User synchronization disabled!';

            return false;
        }

        if (empty($this->api)) {
            echo 'Account setup not finished!';

            return false;
        }

        $unsubscribers_synchronized = $this->removeUnsubscribers(self::UNSUBSCRIBERS_BATCH_LIMIT);
        if (!$unsubscribers_synchronized) {
            echo 'Failed to synchronize unsubscribers from Smaily!';

            Logger::logMessageWithSeverity('Customer sync failed - unsubscribers are not removed', 1);

            return false;
        }

        // Don't sync customers if failed to remove unsubscribers.
        $subscribers_synchronized = $this->sendSubscribersToSmaily(self::SUBSCRIBERS_BATCH_LIMIT);
        if (!$subscribers_synchronized) {
            echo 'Failed to synchronize subscribers from the store!';

            Logger::logMessageWithSeverity('Customer sync failed - failed to send subscribers to Smaily', 1);

            return false;
        }

        echo 'Subscribers synchronization finished!';

        return true;
    }

    /**
     * Get unsubscribers from Smaily and change the subscription status to unsubscribed in store.
     *
     * @param int $limit limit request size
     *
     * @return bool success status
     */
    private function removeUnsubscribers($limit = 1000)
    {
        $offset = 0;

        while (true) {
            $response = $this->api->listUnsubscribers($limit, $offset);

            // Stop if error.
            if ($response->getStatusCode() !== 200) {
                Logger::logErrorWithFormatting('Failed fetching unsubscribers.');

                return false;
            }

            $body = json_decode($response->getBody()->getContents(), true);

            // Stop if no more subscribers.
            if (empty($body)) {
                break;
            }

            // Remove subscribed status for unsubscribers.
            $subscriberCollectionModel = new SubscriberCollection();
            $result = $subscriberCollectionModel->batchUnsubscribeByEmail(
                array_map(
                    function ($item) {
                        return $item['email'];
                    },
                    $body,
                )
            );

            // Stop if query fails.
            if (!$result) {
                Logger::logErrorWithFormatting('Failed removing subscribed status for unsubscribers.');

                return false;
            }

            // Smaily API call offset is considered as page number, not SQL offset!
            ++$offset;
        }

        return true;
    }

    /**
     * Send store subscribers data to Smaily.
     *
     * @param int $limit subscriber request batch limit
     *
     * @return bool success status
     */
    public function sendSubscribersToSmaily(int $limit): bool
    {
        $offset = 0;
        $subscriberCollection = new SubscriberCollection();

        $additionalFields = unserialize($this->configuration->get('SMAILY_SYNCRONIZE_ADDITIONAL'));
        $selectedFields = array_keys(array_filter($additionalFields));

        while (true) {
            $subscribers = $subscriberCollection->getSubscribers($limit, $offset);

            // Stop if query fails.
            if ($subscribers === false) {
                Logger::logErrorWithFormatting('Failed retrieving newsletter subscribers from DB.');

                return false;
            }
            // Stop if no more customers.
            if (empty($subscribers)) {
                break;
            }

            $payload = [];
            foreach ($subscribers as $subscriber) {
                $payload[] = $this->generateSubscriberPayload($subscriber, $selectedFields);
            }

            $this->send($payload);

            $offset += $limit;
        }

        return true;
    }

    private function generateSubscriberPayload(Subscriber $subscriber, array $fields): array
    {
        $userdata = [
            'email' => $subscriber->email,
        ];

        foreach ($fields as $field) {
            switch ($field) {
                case 'first_name':
                    $userdata['first_name'] = $subscriber->firstName;
                    break;
                case 'last_name':
                    $userdata['last_name'] = $subscriber->lastName;
                    break;
                case 'birthday':
                    $userdata['birthday'] = $subscriber->birthDay;
                    break;
                case 'website':
                    $userdata['website'] = $subscriber->website;
                    break;
                default:
                    break;
            }
        }

        return $userdata;
    }

    private function send(array $subscribers): bool
    {
        $response = $this->api->createSubscribers($subscribers);

        if ($response->getStatusCode() !== 200) {
            Logger::logErrorWithFormatting(
                'Failed sending subscribers to Smaily. Smaily HTTP response code: %s',
                $response->getStatusCode(),
            );

            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!array_key_exists('code', $body) || $body['code'] !== 101) {
            Logger::logErrorWithFormatting(
                'Failed sending subscribers to Smaily. Smaily response code: %s, message: %s',
                $body['code'],
                $body['message']
            );

            return false;
        }

        return true;
    }
}
