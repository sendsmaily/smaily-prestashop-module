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

Online documentation and code samples are available via our [Help Center](http://help.smaily.com/en/support/home).

## Contribute

All development for Smaily for Prestashop is [handled via GitHub](https://github.com/sendsmaily/smaily-prestashop-module). Opening new issues and submitting pull requests are welcome.

## Installation

1. Upload or extract the `smailyforprestashop` folder to your site's `/modules/` directory. You can also find this module in **Modules -> Selection** - section in your admin panel - search for Smaily for Prestashop.
2. Install the plugin from the **Modules** - menu in Prestashop.

## Usage

1. Go to Modules -> Modules & Services -> Installed Modules -> Smaily for Prestashop and click configure
2. Insert your Smaily API authentication information to get started.
3. Select if you want to use Cron for contact synchronization between PrestaShop and Smaily
4. Next, click **validate** to check credentials and reveive Autoresponder information.
5. Select your autoresponder and additional fields you want to synchronize (email is automatic)
6. Enter Cron token to make running Cron more secure
7. Click Save Changes
8. Cron is set up to synchronize contacts when CRON-url is visited. To make running cron more secure you can enter
   unique token that is added to url. Use host Cpanel, PrestaShop Cron tasks manager or external cron service to automate process.
9. To use Newsletter Subscription form for direct subscription handling transplant Smaily module in Design ->
   Positions -> Transplant module section. Select Smaily for Prestashop module and displayFooterBefore hook.
10. That's it, your PrestaShop store is now integrated with Smaily Plugin!

## Frequently Asked Questions

### Where I can find data-log for Cron?

Cron update data-log is stored in the root folder of Smaily plugin, inside "smaily-cron.txt" file.

### How can I access additional Abandoned cart parameters in Smaily template editor?

Here is a list of all the parameters available in Smaily email templating engine:

Customer first name: {{ firstname }}.

Customer last name: {{ lastname }}

Store url: {{ store_url }}.

Up to 10 products can be received in Smaily templating engine. You can refrence each product with number 1-10 behind parameter name.

Product name: {{ product_name_[1-10] }}.

Product description: {{ product_description_short_[1-10] }}.

Product quantity: {{ product_quantity_[1-10] }}.

Product price: {{ product_price_[1-10] }}.

Product category: {{ product_category_[1-10] }}.

## Changelog

### 1.1.0 - 2019

- New Feature. Added Abandoned cart support.
- Changed admin page behaviour when API credentials allready validated.

### 1.0.0 - 2018

- This is the first public release.
