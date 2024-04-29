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

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class AbandonedCartFormDataConfiguration implements DataConfigurationInterface
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

        $return['enabled'] = $this->configuration->getBoolean('SMAILY_ENABLE_ABANDONED_CART');
        $return['autoresponder'] = $this->configuration->get('SMAILY_CART_AUTORESPONDER');
        $return['sync_additional'] = json_decode($this->configuration->get('SMAILY_CART_SYNCRONIZE_ADDITIONAL'), true);
        $return['sync_interval'] = (int) $this->configuration->get('SMAILY_ABANDONED_CART_TIME') === 0 ? 15 : $this->configuration->get('SMAILY_ABANDONED_CART_TIME');
        $return['cron_token'] = $this->configuration->get('SMAILY_CART_CRON_TOKEN');
        $return['cron_url'] = $this->buildCronURL($return['cron_token']);

        return $return;
    }

    public function updateConfiguration(array $formData): array
    {
        $errors = [];

        if (empty($formData['cron_token'])) {
            $errors[] = [
                'key' => 'Please provide a cron token for abandoned cart synchronization.',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if ($formData['enabled'] && empty($formData['autoresponder'])) {
            $errors[] = [
                'key' => 'Please select an automation workflow for customer Opt-In trigger.',
                'domain' => 'Modules.Smailyforprestashop.Admin',
                'parameters' => [],
            ];
        }

        if ($this->validateConfiguration($formData) && empty($errors)) {
            $this->configuration->set('SMAILY_ENABLE_ABANDONED_CART', $formData['enabled']);
            $this->configuration->set('SMAILY_CART_AUTORESPONDER', $formData['autoresponder']);
            $this->configuration->set('SMAILY_CART_SYNCRONIZE_ADDITIONAL', json_encode($formData['sync_additional']));
            $this->configuration->set('SMAILY_ABANDONED_CART_TIME', $formData['sync_interval']);
            $this->configuration->set('SMAILY_CART_CRON_TOKEN', $formData['cron_token']);
        }

        return $errors;
    }

    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration['enabled'],
            $configuration['autoresponder'],
            $configuration['sync_additional'],
            $configuration['cron_token']
        );
    }

    /**
     * Get customer cron token or generate random string when not set.
     */
    private function buildCronURL(string $token): string
    {
        return \Context::getContext()
            ->link
            ->getModuleLink(
                'smailyforprestashop',
                'SmailyCartCron',
                ['token' => $token]
            );
    }
}
