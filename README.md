# Smaily for PrestaShop

## Description

Smaily email marketing and automation extension module for PrestaShop.

Automatically subscribe newsletter subscribers to a Smaily subscribers list, generate an RSS feed based on products for easy template import and add a Newsletter Subscription form for an opt-in sign-up form.

## Features

### PrestaShop Newsletter Subscribers

- Use the Subscribe Newsletter modules form to send subscribers directly to the Smaily subscribers list
- Subscribe Newsletter form with CAPTCHA support

### PrestaShop Products RSS-feed

Generate an RSS feed with the 50 latest updated active products for easy import to the Smaily template

### Two-way synchronization between Smaily and PrestaShop

- Remove PrestaShop subscribers based on the Smaily unsubscribed list
- Update unsubscribed status in PrestaShop users database
- Collect and send new user data to Smaily for subscribers in the store
- Generate data log for each update

### Abandoned cart

- Get customer abandoned cart info and send recovery e-mails with Smaily templates.
- Set preferred delay time when the cart is considered abandoned.

## Requirements

Smaily for PrestaShop requires PHP 7.2+ (PHP 8.1+ recommended). You'll also need to be running PrestaShop 8.0+.

## Documentation & Support

Online documentation and code samples are available via our [Help Center](https://smaily.com/help/user-manuals/).

## Contribute

All development for Smaily for PrestaShop is [handled via GitHub](https://github.com/sendsmaily/smaily-prestashop-module). Opening new issues and submitting pull requests are welcome.

## Installation

1. Upload or extract the `smailyforprestashop` folder to your site's `/modules/` directory. You can also find this module in **Modules -> Selection** - section in your admin panel - search for Smaily for PrestaShop.
2. Install the plugin from the **Modules** - menu in PrestaShop.

## Usage

1. Go to Modules -> Module Manager -> Smaily for PrestaShop and click Configure
2. Insert your Smaily API authentication information and click **Connect** to get started.
3. Under the **Customer Sync** tab select if you want to enable customer synchronization.
4. Select additional fields you want to synchronize (email is automatic) and change the cron token if you like your own.
5. New customers who sign up with the newsletter enabled can be added to Smaily by enabling trigger opt-in on customer signup.
6. An autoresponder can be selected for "opt-in on customer sign-up", this will only be triggered if the previous option is enabled.
7. Click **Save** to save customer synchronization settings.
8. Under the **Abandoned Cart** tab select if you want to enable abandoned cart synchronization.
9. Select autoresponder for abandoned cart.
10. Select additional fields to send to the abandoned cart template. Firstname, lastname and store-url are always added.
11. Add delay time when the cart is considered abandoned. Minimum time 15 minutes. Change the cron token if you like your own.
12. Click **Save** to save abandoned cart settings.
13. Cron is set up to synchronize contacts when cron-URL is visited. Use host cPanel, PrestaShop cron tasks manager or external cron service to automate the process.
14. That's it, your PrestaShop store is now integrated with the Smaily Plugin!

## Using the Newsletter Subscription form

1. Navigate to Design -> Positions -> Transplant a Module section.
2. Select the **Newsletter subscription** module in the Module field.
3. Select a hook where you would like to transplant the newsletter form.

## Frequently Asked Questions

### How can I access additional Abandoned cart parameters in the Smaily template editor?

Here is a list of all the parameters available in the Smaily email templating engine:

Customer first name: `{{ first_name }}`.

Customer last name: `{{ last_name }}`.

Up to 10 products can be received in the Smaily templating engine. You can reference each product with a number 1-10 behind the parameter name.

Product name: `{{ product_name_[1-10] }}`.

Product description: `{{ product_description_[1-10] }}`.

Product SKU/Reference: `{{ product_sku_[1-10] }}`.

Product quantity: `{{ product_quantity_[1-10] }}`.

Product price: `{{ product_price_[1-10] }}`.

Product base price : `{{ product_base_price_[1-10] }}`.

Also, you can determine if a customer had more than 10 items in the cart

More than 10 items: `{{ over_10_products }}`.
