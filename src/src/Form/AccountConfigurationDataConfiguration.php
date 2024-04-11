<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class AccountConfigurationDataConfiguration implements DataConfigurationInterface
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

        $return['subdomain'] = $this->configuration->get('SMAILY_SUBDOMAIN');
        $return['username'] = $this->configuration->get('SMAILY_USERNAME');
        $return['password'] = $this->configuration->get('SMAILY_PASSWORD');

        return $return;
    }

    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if (empty($configuration['subdomain'])) {
            $errors[] = [
                'key' => 'Please enter subdomain',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if (empty($configuration['username'])) {
            $errors[] = [
                'key' => 'Please enter username',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if (empty($configuration['password'])) {
            $errors[] = [
                'key' => 'Please enter password',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if ($this->validateConfiguration($configuration) && empty($errors)) {
            $api = new Api($configuration['subdomain'], $configuration['username'], $configuration['password']);
            $response = $api->listAutoresponders();
            switch ($response->getStatusCode()) {
                case 200:
                    $this->configuration->set('SMAILY_SUBDOMAIN', $configuration['subdomain']);
                    $this->configuration->set('SMAILY_USERNAME', $configuration['username']);
                    $this->configuration->set('SMAILY_PASSWORD', $configuration['password']);
                    break;
                case 401:
                    $errors[] = [
                        'key' => 'Unauthorized, please check credentials.',
                        'domain' => 'Modules.Smailyforprestashop.Admin',
                        'parameters' => [],
                    ];
                    break;
                default:
                    $errors[] = [
                        'key' => 'Error validating credentials, please try again.',
                        'domain' => 'Modules.Smailyforprestashop.Admin',
                        'parameters' => [],
                    ];
                    break;
            }
        }

        return $errors;
    }

    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['subdomain'],
            $configuration['username'],
            $configuration['password']
        );
    }
}
