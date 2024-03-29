# Smaily for Prestashop

## Description

Smaily email marketing and automation extension module for PrestaShop.

Automatically subscribe newsletter subscribers to a Smaily subscribers list, generate rss-feed based on products for easy template import and add Newsletter Subscribtion form for opt-in sign-up form.

## Features

### PrestaShop Newsletter Subscribers

- Add subscribers to Smaily subscribers list
- Add new Subscribe Newsletter form to send subscribers directly to Smaily subscribers list
- Subscribe Newsletter form with CAPTCHA support

### PrestaShop Products RSS-feed

- Generate RSS-feed with 50 latest updated active products for easy import to Smaily template

### Two-way synchronization between Smaily and PrestaShop

- Get unsubscribers from Smaily unsubscribed list
- Update unsubscribed status in PrestaShop users database
- Collect new user data for subscribed users
- Generate data log for each update

### Abandoned cart

- Get customer abandoned cart info and send recovery e-mails with Smaily templates.
- Set prefered delay time when cart is considered abandoned.

## Requirements

Smaily for Prestashop requires PHP 5.6+ (PHP 7.0+ recommended). You'll also need to be running Prestashop 1.7+.

## Documentation & Support

Online documentation and code samples are available via our [Help Center](https://smaily.com/help/user-manuals/).

## Contribute

All development for Smaily for Prestashop is [handled via GitHub](https://github.com/sendsmaily/smaily-prestashop-module). Opening new issues and submitting pull requests are welcome.

## Installation

1. Upload or extract the `smailyforprestashop` folder to your site's `/modules/` directory. You can also find this module in **Modules -> Selection** - section in your admin panel - search for Smaily for Prestashop.
2. Install the plugin from the **Modules** - menu in Prestashop.

## Usage

1. Go to Modules -> Module Manager -> Smaily for Prestashop and click Configure
2. Insert your Smaily API authentication information and click **Validate** to get started.
3. Under **Customer Synchronization** tab select if you want to enable customer synchronization.
4. Select additional fields you want to synchronize (email is automatic), change cron token if you like your own.
5. New customers who sign up with newsletter enabled can be added to Smaily by enabling trigger opt-in on customer signup.
6. An autoresponder can be selected for "opt-in on customer sign-up", this will only be triggered if the previous option is enabled.
7. Click **Save** to save customer synchronization settings.
8. Under **Abandoned Cart** tab select if you want to enable abandoned cart synchronization.
9. Select autoresponder for abandoned cart.
10. Select additional fields to send to abandoned cart template. Firstname, lastname and store-url are always added.
11. Add delay time when cart is considered abandoned. Minimum time 15 minutes. Change cron token if you like your own.
12. Click **Save** to save abandoned cart settings.
13. Cron is set up to synchronize contacts when CRON-url is visited. Use host Cpanel, PrestaShop Cron tasks manager or external cron service to automate process.
14. That's it, your PrestaShop store is now integrated with Smaily Plugin!

## Using Newsletter Subscription form

1. Navigate to Design -> Positions -> Transplant a Module section.
2. Select **Smaily for Prestashop** module in Module field.
3. Select hook where you would like to transplant newsletter form. You can chose FooterBefore / LeftColumn / RightColumn.
4. New form is displayed when you have validated your credentials in Smaily for Opencart module settings.

## Frequently Asked Questions

### Where I can find data-log for Cron?

Cron update data-log is stored in the root folder of Smaily plugin, inside "smaily-cron.txt" file.

### How can I access additional Abandoned cart parameters in Smaily template editor?

Here is a list of all the parameters available in Smaily email templating engine:

Customer first name: `{{ first_name }}`.

Customer last name: `{{ last_name }}`.

Up to 10 products can be received in Smaily templating engine. You can reference each product with number 1-10 behind parameter name.

Product name: `{{ product_name_[1-10] }}`.

Product description: `{{ product_description_[1-10] }}`.

Product SKU/Reference: `{{ product_sku_[1-10] }}`.

Product quantity: `{{ product_quantity_[1-10] }}`.

Product price: `{{ product_price_[1-10] }}`.

Product base price : `{{ product_base_price_[1-10] }}`.

Also you can determine if customer had more than 10 items in cart

More than 10 items: `{{ over_10_products }}`.
