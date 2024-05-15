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
    /**
     * @var string
     */
    private $tab;

    /**
     * @var FormInterface
     */
    private $accountForm;

    /**
     * @var FormInterface
     */
    private $customerSyncForm;

    /**
     * @var FormInterface
     */
    private $abandonedCartForm;

    /**
     * @var FormInterface;
     */
    private $rssFeedForm;

    public function index(Request $request): Response
    {
        $this->tab = 'account';

        // Account
        $accountFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.account_form_handler');
        $this->accountForm = $accountFormDataHandler->getForm();
        $this->accountForm->handleRequest($request);

        // Customer Sync
        $customerSyncFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.customer_sync_form_handler');
        $this->customerSyncForm = $customerSyncFormDataHandler->getForm();
        $this->customerSyncForm->handleRequest($request);

        // Abandoned Cart
        $abandonedCartFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.abandoned_cart_form_handler');
        $this->abandonedCartForm = $abandonedCartFormDataHandler->getForm();
        $this->abandonedCartForm->handleRequest($request);

        // RSS-feed
        $rssFeedFormDataHandler = $this->get('prestashop.module.smailyforprestashop.form.rss_feed_form_handler');
        $this->rssFeedForm = $rssFeedFormDataHandler->getForm();
        $this->rssFeedForm->handleRequest($request);

        if ($this->accountForm->get('submit')->isClicked() && $this->accountForm->isValid()) {
            return $this->handleAccountFormSubmit($accountFormDataHandler);
        }

        if ($this->customerSyncForm->get('submit')->isClicked()) {
            $this->tab = 'sync';

            return $this->handleCustomerSyncFormSubmit($customerSyncFormDataHandler);
        }

        if ($this->abandonedCartForm->get('submit')->isClicked()) {
            $this->tab = 'cart';

            return $this->handleAbandonedCartFormSubmit($abandonedCartFormDataHandler);
        }

        if ($this->rssFeedForm->get('submit')->isClicked()) {
            $this->tab = 'rss';

            return $this->handleRssFeedFormSubmit($rssFeedFormDataHandler);
        }

        return $this->renderForms();
    }

    private function handleAccountFormSubmit(FormHandlerInterface $formHandler): Response
    {
        $errors = $formHandler->save($this->accountForm->getData());

        if (empty($errors)) {
            $this->addFlash('success', $this->trans('Connected with Smaily account.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);

        return $this->renderForms();
    }

    private function handleCustomerSyncFormSubmit(FormHandlerInterface $formHandler): Response
    {
        if (!$this->customerSyncForm->isValid()) {
            return $this->renderForms();
        }

        $formData = $this->customerSyncForm->getData();

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

        return $this->renderForms();
    }

    private function handleAbandonedCartFormSubmit(FormHandlerInterface $formHandler): Response
    {
        if (!$this->abandonedCartForm->isValid()) {
            return $this->renderForms();
        }

        $errors = $formHandler->save($this->abandonedCartForm->getData());
        if (empty($errors)) {
            $this->addFlash('success', $this->trans('Configuration saved.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);

        return $this->renderForms();
    }

    private function handleRssFeedFormSubmit(FormHandlerInterface $formHandler): Response
    {
        if (!$this->rssFeedForm->isValid()) {
            return $this->renderForms();
        }

        $errors = $formHandler->save($this->rssFeedForm->getData());
        if (empty($errors)) {
            $this->addFlash('success', $this->trans('RSS-feed URL updated.', 'Modules.Smailyforprestashop.Admin'));

            return $this->redirectToRoute('smailyforprestashop_module_configuration');
        }

        $this->flashErrors($errors);

        return $this->renderForms();
    }

    private function renderForms(): Response
    {
        $configurationService = $this->get('prestashop.adapter.legacy.configuration');
        $isAccountConnected = $configurationService->get('SMAILY_SUBDOMAIN') !== '';

        // Allow to access settings only if account is connected.
        if (!$isAccountConnected) {
            return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
                'accountConfigurationForm' => $this->accountForm->createView(),
                'accountConnected' => false,
                'tab' => $this->tab,
                'jsVariables' => [],
            ]);
        }

        return $this->render('@Modules/smailyforprestashop/views/templates/admin/configuration.html.twig', [
            'accountConfigurationForm' => $this->accountForm->createView(),
            'customerSyncForm' => $this->customerSyncForm->createView(),
            'abandonedCartForm' => $this->abandonedCartForm->createView(),
            'rssFeedForm' => $this->rssFeedForm->createView(),
            'accountConnected' => true,
            'tab' => $this->tab,
            'jsVariables' => [
                'rssBaseURL' => \Context::getContext()->link->getModuleLink('smailyforprestashop', 'SmailyRssFeed'),
            ],
        ]);
    }
}
