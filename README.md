# EBANX Payment Gateway PrestaShop Extension

This plugin allows you to integrate your PrestaShop store with the EBANX payment gateway.
It includes support to installments and custom interest rates.

## Installation
1. Clone the git repo to your PrestaShop root folder
```
git clone --recursive https://github.com/ebanx/ebanx-prestashop.git
```
2. Go to your shop administation area, then to **Modules > Modules**.
3. Find the EBANX module name and click the **Install** button next to it.
4. Enter the integration key you were given by the EBANX integration team. You will need to use different keys in test and production modes.
5. Change the other settings if needed.
6. Click the _Save_ button on the right corner of the screen.
7. Go to the EBANX Merchant Area, then to **Integration > Merchant Options**.
  1. Change the _Status Change Notification URL_ to:
  ```
  {YOUR_SITE}/notify.php
  ```
  2. Change the _Response URL_ to:
  ```
  {YOUR_SITE}/index.php?fc=module&module=ebanx&controller=return
  ```
8. That's all!

## Changelog
* 2.7.0: Change all images adding the EBANX new logo, <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Add Boleto and TEF for EBANX Express, Add integration key validations, <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Add instalments rate configuration in the Admin panel of the module, <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Small code fixes <br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Change PHP lib
* 2.6.1: Bug fixes
* 2.6.0: Created EBANX Express and EBANX Standard Checkout
* 2.5.0: integrated Peru using EBANX Checkout
* 2.4.1: fixed interest message
* 2.4.0: PrestaShop 1.6 compatibility, reviewed forms
* 2.3.2: handle refunds and chargebacks on notification
* 2.3.1: added order number to payment request
* 2.3.0: updated library, added installments
* 2.2.2: fixed SSL redirections
* 2.2.1: fixed mandatory phone field
* 2.2.0: translated error messages
* 2.1.1: fixed hookHeader printing "1" string
* 2.1.0: added installments for CCs, updated ebanx-php lib
* 2.0.0: split payment methods via Direct API, removed EBANX Checkout
* 1.0.2: updated EBANX library
* 1.0.1: remove installments from checkout mode
* 1.0.0: first release
