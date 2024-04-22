<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class CustomerSyncFormDataConfiguration implements DataConfigurationInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $return = [];

        $return['enabled'] = $this->configuration->getBoolean('SMAILY_ENABLE_CUSTOMER_SYNC');
        $return['sync_additional'] = unserialize($this->configuration->get('SMAILY_SYNCRONIZE_ADDITIONAL'));
        $return['cron_token'] = $this->configuration->get('SMAILY_CUSTOMER_CRON_TOKEN');
        $return['cron_url'] = $this->buildCronURL($return['cron_token']);
        $return['optin_enabled'] = $this->configuration->getBoolean('SMAILY_OPTIN_ENABLED');
        $return['autoresponder'] = $this->configuration->get('SMAILY_OPTIN_AUTORESPONDER');

        return $return;
    }

    public function updateConfiguration(array $formData): array
    {
        $errors = [];

        $syncEnabled = $formData['enabled'];
        $additionalFields = $formData['sync_additional'];
        $optInEnabled = $formData['optin_enabled'];
        $cronToken = $formData['cron_token'];
        $autoresponder = $formData['autoresponder'];

        if (empty($cronToken)) {
            $errors[] = [
                'key' => 'Please provide a cron token for customer synchronization.',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if ($optInEnabled && empty($autoresponder)) {
            $errors[] = [
                'key' => 'Please select an automation workflow for customer Opt-In trigger.',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        // Clear autoresponder when disabling customer sign-up;
        if (!$optInEnabled) {
            $autoresponder = null;
        }

        if ($this->validateConfiguration($formData) && empty($errors)) {
            $this->configuration->set('SMAILY_ENABLE_CUSTOMER_SYNC', $syncEnabled);
            $this->configuration->set('SMAILY_SYNCRONIZE_ADDITIONAL', serialize($additionalFields));
            $this->configuration->set('SMAILY_CUSTOMER_CRON_TOKEN', $cronToken);
            $this->configuration->set('SMAILY_OPTIN_ENABLED', $optInEnabled);
            $this->configuration->set('SMAILY_OPTIN_AUTORESPONDER', $autoresponder);

            if ($optInEnabled) {
                // We disable ps_emailsubscription plugin mail sending in order provide a place
                // for sending opt-in emails and to avoid sending duplicate emails to customers.
                // These emails can and should be implemented as a part of the automation workflow.
                $this->configuration->set('NW_VERIFICATION_EMAIL', 0);
                $this->configuration->set('NW_CONFIRMATION_EMAIL', 0);
                $this->configuration->set('NW_VOUCHER_CODE', null);
            }
        }

        return $errors;
    }

    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['enabled'],
            $configuration['sync_additional'],
            $configuration['cron_token'],
            $configuration['optin_enabled'],
            $configuration['autoresponder']
        );
    }

    /**
     * Get customer cron token or generate random string when not set.
     */
    private function buildCronURL(string $token): string
    {
        // TODO: Handler and correct link
        return \Context::getContext()
            ->link
            ->getModuleLink(
                'smailyforprestashop',
                'SmailyCustomerCron',
                [
                    'token' => $token,
                ]
            );
    }
}
