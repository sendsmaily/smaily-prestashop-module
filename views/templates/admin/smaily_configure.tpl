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

{*Error messages*}
<div class="bootstrap">
    <div id ="smaily-messages">
    </div>
</div>
{* Panel for form *}
<div class="panel">
  <div class="panel-heading">
    <span>{l s="Smaily Module Settings" mod='smailyforprestashop'}</span>
    <i class="icon-refresh icon-spin icon-fw" id="smaily-spinner"></i>
  </div>
  {*Navigation links*}
  <ul class="nav nav-tabs" id="myTab" role="tablist">

    <li class="nav-item active">
      <a class="nav-link" data-toggle="tab" href="#general" role="tab">{l s="General" mod='smailyforprestashop'}</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-toggle="tab" href="#customer" role="tab">{l s="Customer Synchronization" mod='smailyforprestashop'}</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="abandoned-cart-tab" data-toggle="tab" href="#cart" role="tab">{l s="Abandoned Cart" mod='smailyforprestashop'}</a>
    </li>
  </ul>
  {* Form content *}
  <div id='mymodule_wrapper' data-token='{$token}'></div>
  <div class="tab-content" style="padding:30px;">
    {* General settings and credentials validation *}
    <div class="tab-pane active" id="general" role="tabpanel">
      <form id="smaily_credentials_form" class="defaultForm form-horizontal" method="post" novalidate="novalidate">
        <div class="form-wrapper">
          <div class="form-group">
            <label class="control-label col-lg-2 required">
              {l s="Subdomain" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <input type="text" name="SMAILY_SUBDOMAIN" id="SMAILY_SUBDOMAIN" value="{$smaily_subdomain}" required="required">
              <p class="help-block">
                {l s="For example demo from https://demo.sendsmaily.net/" mod='smailyforprestashop'}
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2 required">
              {l s="Username" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <input type="text" name="SMAILY_USERNAME" id="SMAILY_USERNAME" value="{$smaily_username}" required="required">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2 required">
              {l s="Password" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <input type="password" name="SMAILY_PASSWORD" id="SMAILY_PASSWORD" value="{$smaily_password}" required="required">
              <p class="help-block">
                  <a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank"> {l s="How to create API credentials?" mod='smailyforprestashop'}</a>
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2">
              {l s="Rss-feed" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <p style="word-wrap:break-word;"><strong>{$smaily_rssfeed_url}</strong></p>
              <p class="help-block"> {l s="Copy this URL into your template editor's RSS block" mod='smailyforprestashop'}</p>
            </div>
          </div>
        </div>
        <div class="panel-footer" id="smaily-validate-form-group">
        {if empty($smaily_password)}
          <button type="button" class="btn btn-default pull-right" id="smaily-validate-credentials">
            <i class="material-icons">done</i>
            <p>
              {l s="Validate" mod='smailyforprestashop'}
            </p>
          </button>
        {else}
          <p>{l s="Credentials are validated, click to remove" mod='smailyforprestashop'}</p>
          <button type="submit" name="smaily_remove_credentials" class="btn btn-default pull-right" id="smaily-remove-credentials">
            <i class="material-icons">delete</i>
            <p>
              {l s="Remove" mod='smailyforprestashop'}
            </p>
          </button>
        {/if}
        </div>
      </form>
    </div>
    {* Customer sync settings *}
    <div class="tab-pane" id="customer" role="tabpanel">
      <form id="smaily_customers_form" class="defaultForm form-horizontal" method="post" novalidate="novalidate">
        <div class="form-wrapper">
          <div class="form-group">
            <label class="control-label col-lg-2">
              {l s="Enable Customer Synchronization" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
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
            <label class="control-label col-lg-2">
              {l s="Syncronize Additional" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
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
            <label class="control-label col-lg-2 required">
              {l s="Cron token" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <input type="text" name="SMAILY_CUSTOMER_CRON_TOKEN" id="SMAILY_CUSTOMER_CRON_TOKEN" value="{$smaily_customer_cron_token}">
              <p class="help-block">
                {l s="Token is required for cron security. Use this auto generated one or replace with your own." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2">
              {l s="Cron url" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <p><strong>{$smaily_customer_cron_url}?token={$smaily_customer_cron_token}</strong></p>
              <p class="help-block">
                {l s="To schedule automatic sync, set up CRON in your hosting and use this URL." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
        </div>
        <div class="panel-footer">
          <button type="submit" name="smaily_submit_configuration" class="btn btn-default pull-right" >
            <i class="process-icon-save"></i>
            {l s="Save" mod='smailyforprestashop' }
          </button>
        </div>
      </form>
    </div>
    {* Abandoned cart settings *}
    <div class="tab-pane" id="cart" role="tabpanel">
      <form id="smaily_abandoned_form" class="defaultForm form-horizontal" method="post" novalidate="novalidate">
        <div class="form-wrapper">
          <div class="form-group">
            <label class="control-label col-lg-2">
              {l s="Enable Abandoned Cart" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
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
            <label class="control-label col-lg-2 required">
              {l s="Autoresponder" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
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
            <label class="control-label col-lg-2">
              {l s="Syncronize Additional" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="first_name" {if 'first_name'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Customer First Name" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="last_name" {if 'last_name'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Customer Last Name" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="name" {if 'name'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Name" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="description" {if 'description'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Description" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="price" {if 'price'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Price" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="base_price" {if 'base_price'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Base Price" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="category" {if 'category'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Category" mod='smailyforprestashop'}
                </label>
              </div>
              <div class="checkbox">
                <label for="SMAILY_CART_SYNCRONIZE_ADDITIONAL">
                  <input type="checkbox" name="SMAILY_CART_SYNCRONIZE_ADDITIONAL[]" value="quantity" {if 'quantity'|in_array:$smaily_cart_syncronize_additional} checked {/if}>
                    {l s="Product Quantity" mod='smailyforprestashop'}
                </label>
              </div>
              <p class="help-block">
                {l s="Select additional fields to send to abandoned cart template." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2 required">
              {l s="Abandoned Cart Delay" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <div class="input-group">
                <input type="text" value="15" min="15" name="SMAILY_ABANDONED_CART_TIME" id="SMAILY_ABANDONED_CART_TIME" value="{$smaily_abandoned_cart_time}">
                <span class="input-group-addon">
                  {l s="Minutes" mod='smailyforprestashop'}
                </span>
              </div>
              <p class="help-block">
                {l s="Time after cart is considered abandoned after last cart edit from customer. Minimum 15 minutes." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2 required">
              {l s="Cron token" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <input type="text" name="SMAILY_CART_CRON_TOKEN" id="SMAILY_CART_CRON_TOKEN" value="{$smaily_cart_cron_token}">
              <p class="help-block">
                {l s="Token is required for cron security. Use this auto generated one or replace with your own." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-lg-2">
              {l s="Cron url" mod='smailyforprestashop'}
            </label>
            <div class="col-lg-10">
              <p><strong>{$smaily_cart_cron_url}?token={$smaily_cart_cron_token}</strong></p>
              <p class="help-block">
                {l s="To schedule automatic sync, set up CRON in your hosting and use this URL." mod='smailyforprestashop'}
              </p>
            </div>
          </div>
        </div>
        <div class="panel-footer">
          <button type="submit" name="smaily_submit_abandoned_cart" class="btn btn-default pull-right" >
            <i class="process-icon-save"></i> 
            {l s="Save" mod='smailyforprestashop' }
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
