{*
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
 {if isset($smaily_subdomain)}
    <div class="block_newsletter col-lg-8 col-md-12 col-sm-12">
        <div class="row">
            <p id="smaily-newsletter-label" class="col-md-5 col-xs-12">{l s="Get our latest news and special sales" mod='smailyforprestashop'}</p>
            <div class="col-md-7 col-xs-12">
                <form action={"https://{$smaily_subdomain}.sendsmaily.net/api/opt-in/"} method="post" autocomplete="off" id="smaily-newsletter-form">
                    <div class="row">
                        <div class="col-xs-12">
                            <input type="hidden" name="key" value="{$smaily_api_key}" />
                            <input type="hidden" name="autoresponder" value="{$smaily_autoresponder}" />
                            <input type="hidden" name="success_url" value="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
                            <input type="hidden" name="failure_url" value="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
                            <input class="btn btn-primary float-xs-right hidden-xs-down" name="submitSmailyNewsletter" type="submit">
                            <div class="input-wrapper">
                            <input name="email" type="email" value="" placeholder="{l s="Your email address" mod='smailyforprestashop'}" aria-labelledby="block-newsletter-label">
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-xs-12">
                            <p>{l s="You may unsubscribe at any moment. For that purpose, please find our contact info in the legal notice." mod='smailyforprestashop'}</p>
                            {if isset($smarty.get.message)}
                            <p class="alert {if $smarty.get.code == 101} alert-success {else} alert-danger {/if}">{$smarty.get.message}</p>
                            {/if}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/if}
