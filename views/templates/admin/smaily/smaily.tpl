<h1>General settings </h1>
<form method="POST" name="zoho_crm" action="" class="validate" novalidate="novalidate">
	<table class="form-table news_letter_module">	
		<tbody>
			<tr class="form-field form-required">
				<th scope="row"><label for="enable">Enable Smaily module <span class="description"></span></label></th>
				<td><input name="enable" value="on" aria-required="true" maxlength="60" type="checkbox" {if !empty($result.enable) && $result.enable == 'on'}checked="checked"{/if} /></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="subdomain">Subdomain <span class="description"></span></label></th>
				<td><input name="subdomain" class="zoho_input" value="{$result.subdomain}" type="text">
				<small id="emailHelp" class="form-text text-muted">For example "demo" from https://demo.sendsmaily.net/</small></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="username">API username <span class="description"></span></label></th>
				<td><input name="username" class="zoho_input" value="{$result.username}" type="text">
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="password">API password <span class="description"></span></label></th>
				<td><input name="password" class="zoho_input" value="{$result.password}" type="password">
				<small id="emailHelp" class="form-text text-muted"><a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank">How to create API credentials?</a></small></td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="password">Subscribe form<span class="description"></span></label></th>
				<td>Set the posiotion of Smaily module in the module and services->positions section in your prestashop Admin <br>and set where you want to show the newsleter.</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="autoresponder">Autoresponder ID (confirmation email) <span class="description"></span></label></th>
				<td>
				
					<select name="autoresponder" class="zoho_input" >
						<option value="">-Select-</option>
						{foreach $autoresponder as $key => $label}
							<option value="{$key}" {if $result.autoresponder == $key}selected="selected"{/if}>{$label}</option>
						{/foreach}
					</select>
					<small id="emailHelp" class="form-text text-muted"><a href="http://help.smaily.com/en/support/solutions/articles/16000017234-creating-an-autoresponder" target="_blank">How to set up an autoresponder for confirmation emails?</a></small>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="syncronize_additional">Syncronize additional fields <span class="description"></span></label></th>
				<td>
				{$syncronize_additional = $result.syncronize_additional|trim}
				{if !empty($syncronize_additional)}
					{$syncronize_additional = ","|explode:$syncronize_additional}
				{else}
					{$syncronize_additional = []}
				{/if}
				
					<select name="syncronize_additional[]" class="zoho_input" multiple style="height:250px;">						
						<option value="subscription_type"{if "subscription_type"|in_array:$syncronize_additional} selected="selected"{/if}>Subscription Type</option>
						<option value="customer_group"{if "customer_group"|in_array:$syncronize_additional} selected="selected"{/if}>Customer Group</option>
						<option value="customer_id"{if "customer_id"|in_array:$syncronize_additional} selected="selected"{/if}>Customer ID</option>
						<option value="prefix"{if "prefix"|in_array:$syncronize_additional} selected="selected"{/if} >Prefix</option>
						<option value="firstname"{if "firstname"|in_array:$syncronize_additional} selected="selected"{/if}>Firstname</option>
						<option value="lastname"{if "lastname"|in_array:$syncronize_additional} selected="selected"{/if}>Lastname</option>
						<option value="gender"{if "gender"|in_array:$syncronize_additional} selected="selected"{/if}>Gender</option>
						<option value="birthday"{if "birthday"|in_array:$syncronize_additional} selected="selected"{/if}>Date Of Birth</option>
						<option value="website"{if "website"|in_array:$syncronize_additional} selected="selected"{/if}>Website</option>
						<option value="store"{if "store"|in_array:$syncronize_additional} selected="selected"{/if}>Store</option>
					</select>
					<small id="emailHelp" class="form-text text-muted">Select fields you wish to synchronize along with subscription data</small>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="rss_feed">Product RSS feed token <span class="description"></span></label></th>
				<td><input name="rss_feed" class="zoho_input" value="{$result.rss_feed}" type="text">
					<small id="emailHelp" class="form-text text-muted">{$base_url}?fc=module&module=smaily&controller=rssfeed&token=[TOKEN]. Copy this URL into your template editor's RSS block, replace [TOKEN] with token value in this field.</small>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row"><label for="rss_feed">How often do you want contacts syncronized? <span class="description"></span></label></th>
				<td>To schedule automatic sync, set up CRON in your hosting and use URL {$base_url}smaily-cron/.</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input name="smailey_submit" class="button button-primary" value="Update" type="submit">
	</p>
</form>
<style>
.zoho_input{ width: 25em !important; } small{ display:block;font-size: 13px; width: 100%;}label { text-align: left !important; padding: 0px 40px 0 0 !important; font-size: 15px;} td, th { padding: 0 0 32px 0 !important; }input.zoho_input { height: 21px; } option { padding: 0 0px 0 6px !important; border-bottom: 1px solid #ddd; }p.submit { text-align: center; }
</style>