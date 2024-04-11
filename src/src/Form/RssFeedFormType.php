<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
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
        // TODO: Why there is extra key?
        $this->productCategoryChoices = $productCategoryChoices['$productCategoryChoices'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_category_id', ChoiceType::class, [
                'label' => $this->trans('Product category', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('Show products only from this category.', 'Modules.Smailyforprestashop.Admin'),
                'choices' => $this->productCategoryChoices,
            ])
            ->add('product_limit', NumberType::class, [
                'label' => $this->trans('Product limit', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('Limit how many products you will add to your feed. Maximum 250.', 'Modules.Smailyforprestashop.Admin'),
                'html5' => true,
                'constraints' => [
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
                'choices' => [
                    $this->trans('Descending', 'Modules.Smailyforprestashop.Admin') => 'desc',
                    $this->trans('Ascending', 'Modules.Smailyforprestashop.Admin') => 'asc',
                ],
            ])
            // TODO: Nice clickable URL.
            ->add('rss_url', TextareaType::class, [
                'label' => $this->trans('RSS-feed URL', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans("Copy this URL into your template editor's RSS block", 'Modules.Smailyforprestashop.Admin'),
                'attr' => [
                    'readonly class' => 'form-control-plaintext',
                    'disabled' => true,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Generate', 'Admin.Actions'),
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}
