<?php
/**
 * 2024 Smaily
 *
 * NOTICE OF LICENSE
 *
 * Smaily for PrestaShop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for PrestaShop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for PrestaShop. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Smaily <info@smaily.com>
 * @copyright 2024 Smaily
 * @license   GPL3
 */
declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\SmailyForPrestaShop\Lib\Rss;
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
        $return['rss_url'] = Rss::buildRssUrl(
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
}
