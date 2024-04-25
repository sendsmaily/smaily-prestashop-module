# Changelog

### 2.0.0

- Total rewrite of the module to support PrestaShop 8 new features and standards. [[#57](https://github.com/sendsmaily/smaily-prestashop-module/pull/57)]
- Module now uses the built-in Newsletter Subscription module to provide functionality for opt-in trigger instead of creating a custom template. This allows to keep internal subscriber addition logic and add required opt-in trigger functionality.

### 1.6.1

- Update user manual links [[#51](https://github.com/sendsmaily/smaily-prestashop-module/pull/51)]

### 1.6.0

- Align customer synchronization first and last name with abandoned cart [[#42](https://github.com/sendsmaily/smaily-prestashop-module/pull/42)]

### 1.5.0

- New feature to trigger opt-in if new customer joins with newsletter enabled [[#32](https://github.com/sendsmaily/smaily-prestashop-module/issues/32)]
- Fix missing template variables [[#33](https://github.com/sendsmaily/smaily-prestashop-module/issues/33)]
- Use PrestashopLogger for logs created by module [[#36](https://github.com/sendsmaily/smaily-prestashop-module/issues/36)]

### 1.4.0

- Added RSS settings tab.
- New options for RSS product feed: order by, order direction, product category.
- Generate RSS feed in more user friendly manner.
- Fix bug which caused invalid CRON URLs, when Friendly URL option was disabled.
- Add Estonian translations for additional fields in abandoned cart settings.

### 1.3.0

- Standardize Abandoned Cart email template parameters across integrations
- `firstname` and `lastname` parameter changed to `first_name` and `last_name`
- `product_description_short` parameter changed to `product_description`
- Added `product_base_price` and `product_sku` parameter
- Removed `product_category` field

### 1.2.2

- Data batching for newsletter subscribers synchronization
- Validating credentials ajax call failing due to wrong post url

### 1.2.1

- Support for PHP 5.6

### 1.2.0

- Changes due to Smaily workflows automation
- Subdomain field parsed when full url entered
- Separate url-s to run customer and cart cron
- Settings page updated for better user-friendliness
- Cron tokens auto generated and url-example now dynamic
- Optimized newsletter subscribe form to use in left/right column of your webpage
- Estonian language translations
- You can now remove validated credentials from admin page
- Subscribe newsletter form sends user language
- Abandoned cart didn't erase email fields that were previously sent
- Customer cron did't get new state of unsubscribed customers before synchronizing with Smaily
- Rss-feed did't show discount and price correctly with taxes

### 1.1.0 - 2019

- New Feature. Added Abandoned cart support
- Changed admin page behaviour when API credentials allready validated

### 1.0.0 - 2018

- This is the first public release
