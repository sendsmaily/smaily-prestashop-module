<?php

declare(strict_types=1);

namespace PrestaShop\Module\SmailyForPrestaShop\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
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
        $accountFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.account_credentials_form_handler');
        $accountForm = $accountFormDataHandler->getForm();
        $accountForm->handleRequest($request);

        if ($accountForm->get('submit')->isClicked() && $accountForm->isValid()) {
            $errors = $accountFormDataHandler->save($accountForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Connected with Smaily account.', 'Modules.Smailyforprestashop.Admin'));

                return $this->redirectToRoute('smailyforprestashop_module_configuration', ['tab' => $tab]);
            }

            $this->flashErrors($errors);
        }

        // Allow to access settings only if account is connected.
        if (!$isAccountConnected) {
            return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
                'accountConfigurationForm' => $accountForm->createView(),
                'accountConnected' => false,
                'tab' => $tab,
            ]);
        }

        // Customer Sync
        $customerSyncFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.customer_sync_form_handler');
        $customerSyncForm = $customerSyncFormDataHandler->getForm();
        $customerSyncForm->handleRequest($request);

        $customerSyncFormClicked = $customerSyncForm->get('submit')->isClicked();

        if ($customerSyncFormClicked) {
            $tab = 'sync';
        }

        if ($customerSyncFormClicked && $customerSyncForm->isValid()) {
            $errors = $customerSyncFormDataHandler->save($customerSyncForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Configuration saved.', 'Modules.Smailyforprestashop.Admin'));

                return $this->redirectToRoute('smailyforprestashop_module_configuration', ['tab' => $tab]);
            }

            $this->flashErrors($errors);
        }

        // Abandoned Cart
        $abandonedCartFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.abandoned_cart_form_handler');
        $abandonedCartForm = $abandonedCartFormDataHandler->getForm();
        $abandonedCartForm->handleRequest($request);
        $abandonedCartFormClicked = $abandonedCartForm->get('submit')->isClicked();

        if ($abandonedCartFormClicked) {
            $tab = 'cart';
        }

        if ($abandonedCartFormClicked && $abandonedCartForm->isValid()) {
            $errors = $abandonedCartFormDataHandler->save($abandonedCartForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Configuration saved.', 'Modules.Smailyforprestashop.Admin'));

                return $this->redirectToRoute('smailyforprestashop_module_configuration', ['tab' => $tab]);
            }

            $this->flashErrors($errors);
        }

        return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
            'accountConfigurationForm' => $accountForm->createView(),
            'customerSyncForm' => $customerSyncForm->createView(),
            'abandonedCartForm' => $abandonedCartForm->createView(),
            'accountConnected' => true,
            'tab' => $tab,
        ]);
    }
}
