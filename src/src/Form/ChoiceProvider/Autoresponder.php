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

namespace PrestaShop\Module\SmailyForPrestaShop\Form\ChoiceProvider;

use PrestaShop\Module\SmailyForPrestaShop\Lib\Api;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Autoresponder implements FormChoiceProviderInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(TranslatorInterface $translator, ConfigurationInterface $configuration)
    {
        $this->translator = $translator;
        $this->configuration = $configuration;
    }

    /**
     * Get autoresponder choices from Smaily.
     */
    public function getChoices(): array
    {
        $noAutoresponders = [
            $this->translator->trans('No automation workflows available', [], 'Module.Smailyforprestashop.Admin') => null,
        ];

        $subdomain = $this->configuration->get('SMAILY_SUBDOMAIN');
        $username = $this->configuration->get('SMAILY_USERNAME');
        $password = $this->configuration->get('SMAILY_PASSWORD');

        if (empty($subdomain) || empty($username) || empty($password)) {
            return $noAutoresponders;
        }

        $api = new Api($subdomain, $username, $password);
        $response = $api->listAutoresponders();

        if ($response->getStatusCode() !== 200) {
            return $noAutoresponders;
        }

        $autoresponders = (array) json_decode($response->getBody()->getContents(), true);
        if (count($autoresponders) == 0) {
            return $noAutoresponders;
        }

        $choices = [
            $this->translator->trans('Select an automation workflow', [], 'Module.Smailyforprestashop.Admin') => null,
        ];
        foreach ($autoresponders as $autoresponder) {
            $choices[$autoresponder['name']] = $autoresponder['id'];
        }

        return $choices;
    }
}
