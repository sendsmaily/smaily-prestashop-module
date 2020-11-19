{*
 * 2018 Smaily
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
 * @copyright 2018 Smaily
 * @license   GPL3
 *}
 {if !empty($smaily_subdomain)}
    <div class="block_newsletter col-lg-8 col-md-12 col-sm-12">
        <div class="row">
            <p id="smaily-newsletter-label" class="col-md-5 col-xs-12">{l s="Get our latest news and special sales" mod='smailyforprestashop'}</p>
            <div class="col-md-7 col-xs-12">
                <form action={"https://{$smaily_subdomain}.sendsmaily.net/api/opt-in/"} method="post" autocomplete="off" id="smaily-newsletter-form">
                    <div class="row">
                        <div class="col-xs-12">
                            <input type="hidden" name="success_url" value="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
                            <input type="hidden" name="failure_url" value="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
                            <input type="hidden" name="language" value="{(isset($language.iso_code)) ? $language.iso_code : ''}" />
                            <input class="btn btn-primary float-xs-right" type="submit" value="{l s="Submit" mod='smailyforprestashop'}" />
                            <div class="input-wrapper">
                            <input name="email" type="email" value="" placeholder="{l s="Your email address" mod='smailyforprestashop'}" aria-labelledby="block-newsletter-label" required />
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-xs-12">
                            <p>{l s="You may unsubscribe at any moment." mod='smailyforprestashop'}</p>
                            {if isset($smarty.get.message)}
                            <p class="alert {if $smarty.get.code == 101} alert-success {else} alert-danger {/if}">
                                {if $smarty.get.code == 101}
                                {l s="Thank you for your subscription!" mod='smailyforprestashop'}
                                {else}
                                {l s="Something went wrong with subscription, please try again!" mod='smailyforprestashop'}
                                {/if}
                            </p>
                            {/if}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/if}
