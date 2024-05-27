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

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbandonedCartFormType extends TranslatorAwareType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $autoresponderChoices;

    /**
     * LinkBlockType constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param array $autoresponderChoices
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        array $autoresponderChoices,
    ) {
        parent::__construct($translator, $locales);
        $this->translator = $translator;
        $this->autoresponderChoices = $autoresponderChoices['$autoresponderChoices'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', SwitchType::class, [
                'label' => $this->trans('Enable Abandoned Cart', 'Modules.Smailyforprestashop.Admin'),
                'required' => false,
            ])
            ->add('autoresponder', ChoiceType::class, [
                'label' => $this->trans('Autoresponder', 'Modules.Smailyforprestashop.Admin'),
                'choices' => $this->autoresponderChoices,
            ])
            ->add(
                $builder->create(
                    'sync_additional',
                    FormType\FormType::class,
                    [
                        'required' => false,
                        'label' => $this->trans('Synchronize Additional', 'Modules.Smailyforprestashop.Admin'),
                        'help' => $this->trans('Select additional fields to send to abandoned cart template.', 'Modules.Smailyforprestashop.Admin'),
                    ]
                )->add(
                    'first_name',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Customers first name', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'last_name',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Customers last name', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'name',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product name', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'description',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product description', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'sku',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product SKU', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'price',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product price', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'base_price',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product base price', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )->add(
                    'quantity',
                    CheckboxType::class,
                    [
                        'label' => $this->trans('Product quantity', 'Modules.Smailyforprestashop.Admin'),
                        'required' => false,
                        'attr' => [
                            'material_design' => true,
                        ],
                    ]
                )
            )
            ->add('sync_interval', NumberType::class, [
                'label' => $this->trans('Abandoned Cart Delay', 'Modules.Smailyforprestashop.Admin'),
                'html5' => true,
                'attr' => [
                    'step' => 1,
                ],
                'help' => $this->trans('Time after cart is considered abandoned after last cart edit from customer. Minimum 15 minutes.', 'Modules.Smailyforprestashop.Admin'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans(
                            'The field is required.',
                            'Modules.Smailyforprestashop.Admin',
                        ),
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 15,
                        'message' => $this->trans(
                            'This value should be greater than %value%',
                            'Modules.Smailyforprestashop.Admin',
                            [
                                '%value%' => 15,
                            ]
                        ),
                    ]),
                ],
            ])
            ->add('cron_token', TextType::class, [
                'label' => $this->trans('Cron token', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('Token is required for cron security. Use this auto generated one or replace with your own.', 'Modules.Smailyforprestashop.Admin'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans(
                            'The field is required.',
                            'Modules.Smailyforprestashop.Admin',
                        ),
                    ]),
                ],
            ])
            ->add('cron_url', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Save', 'Admin.Actions'),
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}
