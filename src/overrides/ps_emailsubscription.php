<?php

declare(strict_types=1);

class Ps_EmailsubscriptionOverRide extends Ps_Emailsubscription
{
    public function getContent()
    {
        $html = parent::getContent();
        $notice = '';

        $configuration = $this->get('prestashop.adapter.legacy.configuration');
        $optInEnabled = $configuration->getBoolean('SMAILY_OPTIN_ENABLED');

        if ($optInEnabled) {
            $notice = '<div class="alert alert-info">' .
            $this->trans('Smaily for PrestaShop modules opt-in automation trigger is active. Set up verification, confirmation and voucher sending in Smaily to avoid sending double emails.', [], 'Modules.Smailyforprestashop.Admin') .
            '<a href="https://smaily.com/help/user-manual/automations/automation-workflows/" target="_blank">' .
            ' ' .
            $this->trans('How to set up automation flow?', [], 'Modules.Smailyforprestashop.Admin') .
            '</a>' .
            '</h1></div>';
        }

        return $notice . $html;
    }
}
