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
<div class="bootstrap" >
    <div class="module_error alert alert-danger" id ="smaily_errormessages" hidden>
    </div>
</div>
<form id="smaily_configuration_form" class="defaultForm form-horizontal mymodule" method="post" novalidate="novalidate">
<div id='mymodule_wrapper' data-token='{$token}'></div>
<div class="panel">
    <div class="panel-heading">
    {l s="Smaily Module Settings" mod='smailyforprestashop'}
    </div>
    <div class="form-wrapper">
        <div class="form-group">
            <label class="control-label col-lg-3">
            {l s="Enable Customer Synchronization" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="SMAILY_ENABLE_CRON" id="Enabled" value="1" {($smaily_enable_cron == 1) ? 'checked' :'' }>
                    <label for="Enabled">{l s="Yes" mod='smailyforprestashop'}</label>
                    <input type="radio" name="SMAILY_ENABLE_CRON" id="Disabled" value="0" {($smaily_enable_cron == 0) ? 'checked' :'' }>
                    <label for="Disabled">{l s="No" mod='smailyforprestashop'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3 required">
            {l s="Subdomain" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <input type="text" name="SMAILY_SUBDOMAIN" id="SMAILY_SUBDOMAIN" value="{$smaily_subdomain}" required="required">
                <p class="help-block">
                    {l s="For example demo from https://demo.sendsmaily.net/" mod='smailyforprestashop'}
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3 required">
            {l s="Username" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <input type="text" name="SMAILY_USERNAME" id="SMAILY_USERNAME" value="{$smaily_username}" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3 required">
            {l s="Password" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <input type="text" name="SMAILY_PASSWORD" id="SMAILY_PASSWORD" value="{$smaily_password}" required="required">
                <p class="help-block">
                    <a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank"> {l s="How to create API credentials?" mod='smailyforprestashop'}</a>
                </p>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-3 required">
            {l s="API key" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <input type="text" name="SMAILY_API_KEY" id="SMAILY_API_KEY" value="{$smaily_api_key}" required="required">
                <p class="help-block">
                    {l s="To use Smaily signup form with captcha enter your api-key" mod='smailyforprestashop'}
                </p>
            </div>
        </div>
        <div class="form-group" id="smaily-validate-form-group" {if !empty($smaily_password)} hidden {/if}>
            <label class="control-label col-lg-3">
            {l s="Validate credentials" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <button type="button" class="btn btn-default" id="smaily-validate-autoresponder">
                    <i class="material-icons">done</i>{l s="Validate" mod='smailyforprestashop'}
                </button>
            </div>
        </div>
        <div id="smaily_autoresponders" {if empty($smaily_password)} hidden {/if}>
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s="Autoresponder" mod='smailyforprestashop'}
                </label>
                <div class="col-lg-9">
                    <select name="SMAILY_AUTORESPONDER" id="SMAILY_AUTORESPONDER">
                        {if empty($smaily_autoresponder)}
                        <option value="">
                            {l s="Select Autoresponder" mod='smailyforprestashop'}
                        </option>
                        {else}
                        <option value='{$smaily_autoresponder|json_encode}'>
                        {$smaily_autoresponder['name']} {l s="(selected)" mod='smailyforprestashop'}
                        </option>
                        {/if}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s="Syncronize Additional" mod='smailyforprestashop'}
                </label>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <label for="SMAILY_SYNCRONIZE_ADDITIONAL">
                            <input type="checkbox" name="SMAILY_SYNCRONIZE_ADDITIONAL[]" value="firstname" {if 'firstname'|in_array:$smaily_syncronize_additional} checked {/if}>
                                {l s="Firstname" mod='smailyforprestashop'}
                        </label>
                    </div>
                    <div class="checkbox">
                        <label for="SMAILY_SYNCRONIZE_ADDITIONAL">
                            <input type="checkbox" name="SMAILY_SYNCRONIZE_ADDITIONAL[]" value="lastname" {if 'lastname'|in_array:$smaily_syncronize_additional} checked {/if}>
                                {l s="Lastname" mod='smailyforprestashop'}
                        </label>
                    </div>
                    <div class="checkbox">
                        <label for="SMAILY_SYNCRONIZE_ADDITIONAL">
                            <input type="checkbox" name="SMAILY_SYNCRONIZE_ADDITIONAL[]" value="birthday" {if 'birthday'|in_array:$smaily_syncronize_additional} checked {/if}>
                                {l s="Birthday" mod='smailyforprestashop'}
                        </label>
                    </div>
                    <div class="checkbox">
                        <label for="SMAILY_SYNCRONIZE_ADDITIONAL">
                            <input type="checkbox" name="SMAILY_SYNCRONIZE_ADDITIONAL[]" value="website" {if 'website'|in_array:$smaily_syncronize_additional} checked {/if}>
                                {l s="Website" mod='smailyforprestashop'}
                        </label>
                    </div>
                    <p class="help-block">
                        {l s="Select additional fields to syncronize" mod='smailyforprestashop'}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s="Rss-feed" mod='smailyforprestashop'}
                </label>
                <div class="col-lg-9">
                    <p><strong>{$smaily_rssfeed_url}</strong></p>
                    <p class="help-block"> {l s="Copy this URL into your template editor's RSS block" mod='smailyforprestashop'}</p>
                </div>
            </div>
            <div class="form-group">
            <label class="control-label col-lg-3">
            {l s="Cron token" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <input type="text" name="SMAILY_CRON_TOKEN" id="SMAILY_CRON_TOKEN" value="{$smaily_cron_token}">
                <p class="help-block">
                    {l s="If left blank anyone can run cron with cron url." mod='smailyforprestashop'}
                </p>
            </div>
        </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s="Cron url" mod='smailyforprestashop'}
                </label>
                <div class="col-lg-9">
                    <p><strong>{$smaily_cron_url}?token=[token]</strong></p>
                    <p class="help-block"> {l s="To schedule automatic sync, set up CRON in your hosting and use this URL, replace token with settings value for security." mod='smailyforprestashop'}</p>
                </div>
            </div>
        </div>
    </div><!-- /.form-wrapper -->
    <div class="panel-footer" {if empty($smaily_password)} hidden {/if}>
    <button type="submit" name="smaily_submit_configuration" class="btn btn-default pull-right" >
        <i class="process-icon-save"></i> 
        {l s="Save" mod='smailyforprestashop' }
    </button>
    </div>
</div>
<!-- Second panel for abandoned cart -->
<div class="panel">
    <div class="panel-heading">
    {l s="Smaily Abandoned Cart Settings" mod='smailyforprestashop'}
    </div>
    <div class="form-wrapper">
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s="Enable Abandoned Cart" mod='smailyforprestashop'}
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="SMAILY_ENABLE_ABANDONED_CART" id="smaily_abandoned_cart_enable_on" value="1" {($smaily_enable_abandoned_cart == 1) ? 'checked' :'' }>
                <label for="smaily_abandoned_cart_enable_on">{l s="Yes" mod='smailyforprestashop'}</label>
                <input type="radio" name="SMAILY_ENABLE_ABANDONED_CART" id="smaily_abandoned_cart_enable_off" value="0" {($smaily_enable_abandoned_cart == 0) ? 'checked' :'' }>
                <label for="smaily_abandoned_cart_enable_off">{l s="No" mod='smailyforprestashop'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s="Abandoned Cart Delay" mod='smailyforprestashop'}
        </label>
        <div class="col-lg-9">
            <div class="input-group">
                <input type="text" name="SMAILY_ABANDONED_CART_TIME" id="SMAILY_ABANDONED_CART_TIME" value="{$smaily_abandoned_cart_time}">
                <span class="input-group-addon">
                    {l s="Hour(s)" mod='smailyforprestashop'}
                </span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s="Autoresponder" mod='smailyforprestashop'}
        </label>
        <div class="col-lg-9">
            <select name="SMAILY_CART_AUTORESPONDER" id="SMAILY_CART_AUTORESPONDER">
                {if empty($smaily_cart_autoresponder)}
                <option value="">
                    {l s="Select Autoresponder" mod='smailyforprestashop'}
                </option>
                {else}
                <option value='{$smaily_cart_autoresponder|json_encode}'>
                    {$smaily_cart_autoresponder['name']} {l s="(selected)" mod='smailyforprestashop'}
                </option>
                {/if}
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s="Syncronize Additional" mod='smailyforprestashop'}
        </label>
        <div class="col-lg-9">
            <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                    <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="name" {if 'name'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                        {l s="Product Name" mod='smailyforprestashop'}
                </label>
            </div>
            <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                    <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="description_short" {if 'description_short'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                        {l s="Description" mod='smailyforprestashop'}
                </label>
            </div>
            <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                    <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="price" {if 'price'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                        {l s="Price" mod='smailyforprestashop'}
                </label>
            </div>
            <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                    <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="category" {if 'category'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                        {l s="Category" mod='smailyforprestashop'}
                </label>
            </div>
            <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                    <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="quantity" {if 'quantity'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                        {l s="Quantity" mod='smailyforprestashop'}
                </label>
            </div>
            <p class="help-block">
                {l s="Select additional fields to send to abandoned cart template. Firstname, lastname and store-url are always added." mod='smailyforprestashop'}
            </p>
        </div>
    </div>
    </div><!-- /.form-wrapper -->
    <div class="panel-footer">
    <button type="submit" name="smaily_submit_abandoned_cart" class="btn btn-default pull-right" >
        <i class="process-icon-save"></i> 
        {l s="Save" mod='smailyforprestashop' }
    </button>
    </div>
</div>
</form>