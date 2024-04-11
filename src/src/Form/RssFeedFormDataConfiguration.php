<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class RssFeedFormDataConfiguration implements DataConfigurationInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public const PRODUCT_LIMIT_MAX_VALUE = 250;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $return = [];

        $return['product_category_id'] = $this->configuration->get('SMAILY_RSS_CATEGORY_ID');
        $return['product_limit'] = $this->configuration->get('SMAILY_RSS_LIMIT');
        $return['sort_by'] = $this->configuration->get('SMAILY_RSS_SORT_BY');
        $return['sort_order'] = $this->configuration->get('SMAILY_RSS_SORT_ORDER');
        $return['rss_url'] = $this->buildRssUrl(
            $return['product_category_id'],
            $return['product_limit'],
            $return['sort_by'],
            $return['sort_order']
        );

        return $return;
    }

    public function updateConfiguration(array $formData): array
    {
        $errors = [];

        if ($formData['product_limit'] > $this::PRODUCT_LIMIT_MAX_VALUE) {
            $errors[] = [
                'key' => 'Maximum number or products is %value%',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [
                    '%value%' => $this::PRODUCT_LIMIT_MAX_VALUE,
                ],
            ];
        }

        if ($this->validateConfiguration($formData) && empty($errors)) {
            $this->configuration->set('SMAILY_RSS_CATEGORY_ID', $formData['product_category_id']);
            $this->configuration->set('SMAILY_RSS_LIMIT', $formData['product_limit']);
            $this->configuration->set('SMAILY_RSS_SORT_BY', $formData['sort_by']);
            $this->configuration->set('SMAILY_RSS_SORT_ORDER', $formData['sort_order']);
        }

        return $errors;
    }

    public function validateConfiguration(array $formData): bool
    {
        return isset(
            $formData['product_limit'],
            $formData['sort_by'],
            $formData['sort_order']
        );
    }

    /**
     * Make RSS URL with query parameters.
     *
     * @return string
     */
    public function buildRssUrl(string $categoryId, string $limit, string $sortBy, string $sortOrder): string
    {
        $query_arguments = [
            'limit' => $limit,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];

        if (!empty($categoryId)) {
            $query_arguments['category_id'] = $categoryId;
        }

        return \Context::getContext()->link->getModuleLink(
            'smailyforprestashop',
            'SmailyRssFeed',
            $query_arguments
        );
    }
}
