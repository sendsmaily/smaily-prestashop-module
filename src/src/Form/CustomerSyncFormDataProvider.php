<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class CustomerSyncFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DataConfigurationInterface
     */
    private $configuration;

    public function __construct(DataConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getData(): array
    {
        return $this->configuration->getConfiguration();
    }

    public function setData(array $data): array
    {
        return $this->configuration->updateConfiguration($data);
    }
}
