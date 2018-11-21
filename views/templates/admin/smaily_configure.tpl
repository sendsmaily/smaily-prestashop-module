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
            {l s="Enable Cron" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <div class="radio t">
                <label><input type="radio" name="SMAILY_ENABLE_CRON" id="Disabled" value="0" {($smaily_enable_cron == 0) ? 'checked' :'' }>Disabled</label>
                </div>
                <div class="radio t">
                <label><input type="radio" name="SMAILY_ENABLE_CRON" id="Enabled" value="1" {($smaily_enable_cron == 1) ? 'checked' :'' }>Enabled</label>
                </div>
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
        <div class="form-group" id="smaily-validate-form-group">
            <label class="control-label col-lg-3">
            {l s="Validate credentials" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-9">
                <button type="button" class="btn btn-default" id="smaily-validate-autoresponder">
                    <i class="material-icons">done</i>{l s="Validate" mod='smailyforprestashop'}
                </button>
            </div>
        </div>
        <div id="smaily_autoresponders" hidden>
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s="Autoresponder" mod='smailyforprestashop'}
                </label>
                <div class="col-lg-9">
                    <select name="SMAILY_AUTORESPONDER" class=" fixed-width-xl" id="SMAILY_AUTORESPONDER">
                        <option value="">
                            {l s="Select Autoresponder" mod='smailyforprestashop'}
                        </option>
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
        </div id="smaily_autoresponders">
    </div><!-- /.form-wrapper -->
    <div class="panel-footer" hidden>
    <button type="submit" name="smaily_submit_configuration" class="btn btn-default pull-right" >
        <i class="process-icon-save"></i> 
        {l s="Save" mod='smailyforprestashop' }
    </button>
    </div>

</div>
</form>