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
