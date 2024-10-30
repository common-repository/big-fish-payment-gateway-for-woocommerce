=== BIG FISH Payment Gateway for WooCommerce ===
Contributors: BIG FISH Kft.
Tags: payment, online payment, credit card, szep card, online trade loan
Requires at least: 4.9
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 3.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

BIG FISH Payment Gateway is available now for webshops as a WooCommerce module.

== Description ==

BIG FISH Payment Gateway system provides more different payment solutions for webshops, where all the payment methods of the webshop can be managed in one place. The appearance of the user side of our module is simple and clear, the task of the customer is only to choose the most convenient payment method and to provide transaction data on the landing page of the bank / payment service provider reached through the module's safe gateway.

Configuration of the module is very simple, anyone is able to treat the interface. Administrators can find payment method settings and general settings by clicking on WooCommerce / Settings / Payments and then on the name of the relevant payment method or the module.

At general settings of the module administrators can set up or modify the name of the webshop in the BIG FISH Payment Gateway system ("Store name") and the API key ("API key") that is required for the authentication. If administrators want to shift the module to test or live operation, they can do it by switching "Test mode" ("Yes" or "No"). In the list of available banks or payment service providers ("Available providers") administrators can see all of our partners whose solutions can be implemented into your webshop by our module.

Every payment method of the merchant's BIG FISH Payment Gateway account can be configured in the module separately. An administrator can set up each payment methods to test or live status and provide the names appeared on the page of the payment method ("Display name"). If the payment method supports both immediate and delayed payments "two steps payments" this option ("Authorisation") can be configured in the module ("Immediate" or "Later"). In the case of setting two steps payment method, you will always need to provide "Encrypt public key".

BIG FISH Payment Gateway module can solve rapid exchange of information between the webshop and the banks/payment service providers, so the administrator can see the system messages related to a transaction of a specific order in real time (in "Order Notes" panel) and the status of the payment of each order. The status of orders (transactions) can be:


* started ("Pending payment"),
* in progress ("Processing"),
* on hold ("On hold"),
* finished/authorised ("Completed"),
* cancelled ("Cancelled"),
* refunded ("Refunded") and
* Failed ("Failed").

Approval of two steps payment transactions can be set by switching the status of the payment to "Completed" or "Cancelled".

= The steps required to use our services are as follows =

1\. Contracting with us:
The online contracting process can be initiated by clicking on the following link and choosing the suitable package: <a href="https://www.paymentgateway.hu/arak" target="_blank">Tariff packages and contracting</a>

The language of contract and communication is Hungarian.

2\. Contracting with the selected payment service provider(s) for online card acceptance:
Our company is a payment technology platform (not a bank or a payment service provider), therefore to use our solution, you need to have an active contract with at least one PSP available on our system.

The list of payment service providers available in our system can be found under the following link: <a href="https://www.paymentgateway.hu/partnereink" target="_blank">Our partners</a>

3\. Connecting to BIG FISH Payment Gateway:
Your IT personnel can examine the integration opportunities even before signing the contract with us. The module is free to use in the test environment. Using the production environment requires an active contract with us and the <a href="https://www.paymentgateway.hu/fejlesztoknek/egyeb/elesitesi-kovetelmenyek" target="_blank">requirements</a> must be met.

Should you need any further information, please do not hesitate to contact us through the [it@paymentgateway.hu](mailto:it@paymentgateway.hu) email address.

== Installation ==

= Automatic installation =
1. Log in WordPress Admin site.
2. Choose "Plugins > Add New Plugin" option from menu on the left side of the page.
3. To "Search Plugins..." box please write "BIG FISH Payment Gateway for WooCommerce" and press Enter.
4. There you will find "BIG FISH Payment Gateway for WooCommerce" plugin. Click on "Install Now" for installation.

= Manual installation =
1. Load BIG FISH Payment Gateway plugin in zip format.
2. Log in WordPress Admin site.
3. Choose "Plugins > Add New" option from menu on the left side.
4. Click on "Upload Plugin" and choose BIG FISH Payment Gateway plugin zip file.
5. Then click on "OK" button and press "Install Now".

= Note =
* After successful installation please activate the plugin by pressing "Activate Plugin" button.
* Please select "WooCommerce > Settings" option from menu on the left side and click on "Payments" tab.
* You can configurate plugin at "BIG FISH Payment Gateway" option.

= Checkout settings (In case of WooCommerce version 8.3 or newer) =
1. Log in the WordPress administration interface.
2. Select the "Pages" menu item from the left menu.
3. In the list that appears, find the line "Checkout — Checkout Page" and click on the word "Checkout".
4. On the Checkout page that appears, find the "Payment options" block and click on it.
5. A "Switch to classic checkout" button appears on the left. Click it.
6. In the pop-up "Switch to classic checkout" panel, click the "Switch" button.
7. Click the "Update" button in the upper right corner.

== Frequently Asked Questions ==

= What are the advantages of choosing Payment Gateway solution? =
Payment Gateway provides technical solution to webshops for connecting to more banks / payment service providers easily, so you do not need to integrate any or all of them each, performing very complex developments. This way the webshop's operator can save time and money. Later it is very easy to connect more new banks /payment service providers or change any of them.

= Is it enough to make an agreement with BIG FISH or do I need to do it with the banks / payment service providers as well? =
You need to sigh an agreement both with the chosen banks / payment service providers and with BIG FISH. Agreement with the banks / psp-s is completely independent from us. Then you will need to pay their fees and BIG FISH fees as well.

= Can you help us to contact banks? =
Off course, we can provide contact details to any of them.

= Do we need to pay only BIG FISH monthly fees for the transactions or chosen banks will debit us with any other fees too? =
For transactions you will have to pay both to us and to the banks / payment service providers. Bank commissions are usually between 1,2-1,8%, but there are no general rules, they offer special prices to each merchants.

= Is it possible to change monthly fee package later any time? =
Yes, at the end of any settlement periods.

== Screenshots ==

1. Appearance of online payment methods available for the customers in the webshop. You can choose.
2. Possibilities of  BIG FISH Payment Gateway WordPress module configuration.
3. Example of the possible configurations of a particular payment method (OTP credit card payment - two participants)
4. Order details: changing of order status and order notes box.

== Changelog ==
= 3.0.1 =
Restore contributors

= 3.0.0 =
Replaced bigfish-hu/payment-gateway-php-sdk v3.18.1 with bigfish-hu/payment-gateway-php7-sdk v3.19.0

Tested on WordPress 6.4.3 and WooCommerce 8.6.1 and PHP 8.3.3

= 2.2.1 =
Fixed:

* PHP Warnings
* K&H Bank One-click payment with preauthorization.
* Using OTP SZÉP with StoreName and ApiKey different from the default settings.
* If the status of the transaction is PENDING, the payer is redirected to the summary page of the order instead of the cancellation page.

Updated:

* MBH SZÉP payment method cannot be switched to embedded mode.

= 2.2.0 =
Update bigfish-hu/payment-gateway-php-sdk to v3.18.1

Added K&H Bank PSD2 compliant CIT payment (card registration and one click payment)

Added K&H Bank Authorization later option

= 2.1.7 =
Saferpay renamed to Worldline

= 2.1.6 =
MKB Bank renamed to MBH Bank due to change brand name

= 2.1.5 =
FIX SCA shipping data if are no shipping data.

= 2.1.4 =
Budapest Bank trade loan renamed to MKB online trade loan due to brand merge

= 2.1.3 =
FIX PSD2 SCA sending data in Guest mode

= 2.1.2 =
Update OTP SZEP voucher

Tested on WooCommerce 5.2.0 and WordPress 5.7

= 2.1.1 =
Update CIB result messages

= 2.1.0 =
Update bigfish-hu/payment-gateway-php-sdk to v3.5.0

Update SimplePay result messages

Enable token cancellation for all providers that can OneClick payment

Handle PENDING and OPEN status response

Handle PSD2 - SCA CountryCode2

New Providers:

* OTP Bank trade loan
* PayPal REST
* PayU REST
* Wirecard

Deprecated Providers:

* Escalion
* FHB Bank
* IPG
* OTP Bank (two participants)
* OTP Multipont
* OTPay
* OTPay MasterPass
* SMS
* VirPAY
* Wirecard QPAY

Tested on WooCommerce 3.7.0 and WordPress 5.5.1

= 2.0.0 =
* PSD2 - SCA (Strong Customer Authentication) update
* In checkout the buyer able to select from tokenized cards.
* Update bigfish-hu/payment-gateway-php-sdk to v3.0.1

Tested on WooCommerce 3.7.0 and WordPress 5.2.3

= 1.4.0 =
SZEP card pocket select changed to multiselect. In checkout the buyer able to select from enabled pockets.
Budapest Bank trade loan with order amount range limit settings
Update bigfish-hu/payment-gateway-php-sdk to v2.14.0
New Provider:
* Budapest Bank trade loan

Tested on WooCommerce 3.6.2 and WordPress 5.2

= 1.3.0 =
bugfix of payment providers multiselector.
Supporting WP Multilang, and WPML
Translatable payment provider's display name, description and return transaction notification from .po file. (EN, DE, HU)
Update bigfish-hu/payment-gateway-php-sdk to v2.13.0
New Provider:
* Global Payments with One Click Payment
* VirPAY with One Click Payment

Tested on WooCommerce 3.5.6 and WordPress 5.1

= 1.2.1 =
bugfix under php 5.5 version

= 1.2.0 =
New Provider:

* Borgun RPG
* Card registration and One Click Payment (Borgun RPG, SIX Payment - Saferpay)

Update plugin to WooCommerce 3.2.6
Tested on WordPress 4.9.1

= 1.1.0 =
Update plugin to WooCommerce 3.0.8
Tested on WordPress 4.7.5 and 4.8

= 1.0.6 =
New Providers:

* Intelligent Payments (IPG)
* Barion Smart Gateway

Editable payment type description

PayPal recurring payment

Provider transaction id into refund email

Fix double close request

= 1.0.5 =
New option in MKB SZÉP Card: Add card data on Payment Gateway page

Update plugin to WooCommerce 2.6.4

= 1.0.4 =
New Providers:

* OTP Simple
* OTP Simple Wire
* PayU (Poland)
* paysafecard
* Saferpay - SIX Payment Services

Stopped Providers:

* Barion
* MasterCard Mobile
* PayU
* PayU Cash
* PayU Wire

Adjustable payment types (Wirecard QPAY)

Version control

= 1.0.3 =
Show Transaction ID in case of error

= 1.0.2 =
Success return url fixed

= 1.0.1 =
Checkout success link fixed

= 1.0.0 =
First edition.
**Wordpress WooCommerce plugin minimum 2.3.6 version is needed.**

Available Providers:

* Barion
* Borgun
* CIB Bank
* Escalion
* FHB Bank
* K&H Bank
* K&H SZÉP Card
* MasterCard Mobile
* MasterPass
* MKB SZÉP Card
* OTP Bank
* OTP Bank (two participants)
* OTP Multipont
* OTP SZÉP Card
* OTPay
* PayPal
* PayU
* PayU Cash
* PayU Wire
* SMS
* Sofort Banking
* UniCredit Bank
* Wirecard QPAY
