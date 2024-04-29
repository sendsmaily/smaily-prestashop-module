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

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            $this->translator->trans('All products', [], 'Modules.Smailyforprestashop.Admin') => null,
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
