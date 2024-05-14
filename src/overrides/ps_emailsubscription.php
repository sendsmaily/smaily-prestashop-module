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

if (!defined('_PS_VERSION_')) {
    exit;
}

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
            $this->trans('How to set up an automation flow?', [], 'Modules.Smailyforprestashop.Admin') .
            '</a>' .
            '</h1></div>';
        }

        return $notice . $html;
    }
}
