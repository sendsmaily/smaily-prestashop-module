<?php

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
            $this->translator->trans('No autoresponders available', [], 'Module.Smailyforprestashop.Admin') => null,
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

        $choices = [];
        foreach ($autoresponders as $autoresponder) {
            $choices[$autoresponder['name']] = $autoresponder['id'];
        }

        return $choices;
    }
}
