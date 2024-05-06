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

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleConfigurationController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $configurationService = $this->get('prestashop.adapter.legacy.configuration');
        $isAccountConnected = $configurationService->get('SMAILY_SUBDOMAIN') !== '';
        $tab = 'account';

        // Account
        $accountFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.account_form_handler');
        $accountForm = $accountFormDataHandler->getForm();
        $accountForm->handleRequest($request);

        // Customer Sync
        $customerSyncFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.customer_sync_form_handler');
        $customerSyncForm = $customerSyncFormDataHandler->getForm();
        $customerSyncForm->handleRequest($request);

        // Abandoned Cart
        $abandonedCartFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.abandoned_cart_form_handler');
        $abandonedCartForm = $abandonedCartFormDataHandler->getForm();
        $abandonedCartForm->handleRequest($request);

        // RSS-feed
        $rssFeedFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.rss_feed_form_handler');
        $rssFeedForm = $rssFeedFormDataHandler->getForm();
        $rssFeedForm->handleRequest($request);

        if ($accountForm->get('submit')->isClicked() && $accountForm->isValid()) {
            $this->handleAccountFormSubmit($accountFormDataHandler, $accountForm);
        }

        if ($customerSyncForm->get('submit')->isClicked() && $customerSyncForm->isValid()) {
            $tab = 'sync';
            $this->handleCustomerSyncFormSubmit($customerSyncFormDataHandler, $customerSyncForm);
        }

        if ($abandonedCartForm->get('submit')->isClicked() && $abandonedCartForm->isValid()) {
            $tab = 'cart';
            $this->handleAbandonedCartFormSubmit($abandonedCartFormDataHandler, $abandonedCartForm);
        }

        if ($rssFeedForm->get('submit')->isClicked() && $rssFeedForm->isValid()) {
            $tab = 'rss';
            $this->handleRssFeedFormSubmit($rssFeedFormDataHandler, $rssFeedForm);
        }

        // Allow to access settings only if account is connected.
        if (!$isAccountConnected) {
            return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
                'accountConfigurationForm' => $accountForm->createView(),
                'accountConnected' => false,
                'tab' => $tab,
                'jsVariables' => [],
            ]);
        }

        return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
            'accountConfigurationForm' => $accountForm->createView(),
            'customerSyncForm' => $customerSyncForm->createView(),
            'abandonedCartForm' => $abandonedCartForm->createView(),
            'rssFeedForm' => $rssFeedForm->createView(),
            'accountConnected' => true,
            'tab' => $tab,
            'jsVariables' => [
                'rssBaseURL' => \Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyRssFeed'),
            ],
        ]);
    }

    private function handleAccountFormSubmit(FormHandlerInterface $formHandler, FormInterface $form)
    {
        $errors = $formHandler->save($form->getData());

        if (empty($errors)) {
            $this->addFlash('success', $this->trans('Connected with Smaily account.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);
    }

    private function handleCustomerSyncFormSubmit(FormHandlerInterface $formHandler, FormInterface $form)
    {
        $formData = $form->getData();
        $errors = $formHandler->save($formData);

        if (empty($errors)) {
            $this->addFlash('success', $this->trans('Configuration saved.', 'Modules.Smailyforprestashop.Admin'));
            if ($formData['optin_enabled']) {
                $this->addFlash(
                    'success',
                    $this->trans(
                        'You have selected an automation to trigger opt-in email sending. We have disabled Newsletter Subscription plugins verification, confirmation and voucher email sending in order to avoid duplicate emails!',
                        'Modules.Smailyforprestashop.Admin',
                    )
                );
            }

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);
    }

    private function handleAbandonedCartFormSubmit(FormHandlerInterface $formHandler, FormInterface $form)
    {
        $errors = $formHandler->save($form->getData());

        if (empty($errors)) {
            $this->addFlash('success', $this->trans('Configuration saved.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);
    }

    private function handleRssFeedFormSubmit(FormHandlerInterface $formHandler, FormInterface $form)
    {
        $errors = $formHandler->save($form->getData());

        if (empty($errors)) {
            $this->addFlash('success', $this->trans('RSS-feed URL updated.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);
    }
}
