<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AccountConfigurationFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subdomain', TextType::class, [
                'label' => $this->trans('Subdomain', 'Modules.Smailyforprestashop.Admin'),
                'help' => $this->trans('For example demo from https://demo.sendsmaily.net', 'Modules.Smailyforprestashop.Admin'),
            ])
            ->add('username', TextType::class, [
                'label' => $this->trans('Username', 'Modules.Smailyforprestashop.Admin'),
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->trans('Password', 'Modules.Smailyforprestashop.Admin'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Connect', 'Modules.Smailyforprestashop.Admin'),
                'attr' => [
                    'class' => 'btn-primary',
                ],
            ]);
    }
}
