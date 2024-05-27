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

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Contracts\Translation\TranslatorInterface;

class RssFeedFormType extends TranslatorAwareType
{
    /**
     * @var TranslatorInterface;
     */
    private $translator;

    /**
     * @var array
     */
    private $productCategoryChoices;

    /**
     * LinkBlockType constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param array $productCategoryChoices
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        array $productCategoryChoices,
    ) {
        parent::__construct($translator, $locales);
        $this->translator = $translator;
        $this->productCategoryChoices = $productCategoryChoices['$productCategoryChoices'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_category_id', ChoiceType::class, [
                'label' => $this->trans('Product category', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('Show products only from this category.', 'Modules.Smailyforprestashop.Admin'),
                'choices' => $this->productCategoryChoices,
                'attr' => [
                    'class' => 'smaily-rss-options',
                ],
            ])
            ->add('product_limit', NumberType::class, [
                'label' => $this->trans('Product limit', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('Limit how many products you will add to your feed. Maximum 250.', 'Modules.Smailyforprestashop.Admin'),
                'html5' => true,
                'attr' => [
                    'class' => 'smaily-rss-options',
                ],
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => $this->trans(
                            'This value should be greater than %value%',
                            'Modules.Smailyforprestashop.Admin',
                            [
                                '%value%' => 0,
                            ]
                        ),
                    ]),
                    new LessThanOrEqual([
                        'value' => 250,
                        'message' => $this->trans(
                            'This value should be less than %value%',
                            'Modules.Smailyforprestashop.Admin',
                            [
                                '%value%' => 250,
                            ]
                        ),
                    ]),
                ],
            ])
            ->add('sort_by', ChoiceType::class, [
                'label' => $this->trans('Sort by', 'Modules.Smailyforprestashop.Admin'),
                'attr' => [
                    'class' => 'smaily-rss-options',
                ],
                'choices' => [
                    $this->trans('Date added', 'Modules.Smailyforprestashop.Admin') => 'date_add',
                    $this->trans('Date updated', 'Modules.Smailyforprestashop.Admin') => 'date_upd',
                    $this->trans('Product name', 'Modules.Smailyforprestashop.Admin') => 'name',
                    $this->trans('Product price', 'Modules.Smailyforprestashop.Admin') => 'price',
                    $this->trans('Product ID', 'Modules.Smailyforprestashop.Admin') => 'id_product',
                ],
            ])
            ->add('sort_order', ChoiceType::class, [
                'label' => $this->trans('Sort order', 'Modules.Smailyforprestashop.Admin'),
                'attr' => [
                    'class' => 'smaily-rss-options',
                ],
                'choices' => [
                    $this->trans('Descending', 'Modules.Smailyforprestashop.Admin') => 'desc',
                    $this->trans('Ascending', 'Modules.Smailyforprestashop.Admin') => 'asc',
                ],
            ])
            ->add('rss_url', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Save', 'Admin.Actions'),
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}
