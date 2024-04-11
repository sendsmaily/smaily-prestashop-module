<?php

namespace PrestaShop\Module\SmailyForPrestaShop\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Adapter\Category\CategoryDataProvider;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductCategory implements FormChoiceProviderInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CategoryDataProvider
     */
    private $categoryDataProvider;

    public function __construct(TranslatorInterface $translator, CategoryDataProvider $categoryDataProvider)
    {
        $this->translator = $translator;
        $this->categoryDataProvider = $categoryDataProvider;
    }

    /**
     * Get autoresponder choices from Smaily.
     */
    public function getChoices(): array
    {
        $choices = [
            $this->translator->trans('All products', [], 'Module.Smailyforprestashop.Admin') => null,
        ];

        return array_merge(
            $choices,
            $this->recursivelyNormalizeCategories($this->categoryDataProvider->getNestedCategories())
        );
    }

    /**
     * Recursively go through categories in array and normalize them.
     *
     * @param array $categories enabled categories in PrestaShop catalog
     *
     * @return array categories in format: array(category name => category id)
     */
    private function recursivelyNormalizeCategories($categories)
    {
        $normalized = [];

        foreach ($categories as $category) {
            $normalized[$category['name']] = $category['id_category'];
            if (isset($category['children']) && is_array($category['children'])) {
                $normalized += $this->recursivelyNormalizeCategories($category['children']);
            }
        }

        return $normalized;
    }
}
