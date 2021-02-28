=== Cities Shipping Zones for WooCommerce ===
Contributors: condless
Tags: dropdown, cities, shipping zone, shipping method
Requires at least: 5.2
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce plugin for turning the state field into a dropdown city field. To be used as Shipping Zones.

== Description ==

WooCommerce plugin for turning the state field into a dropdown city field. To be used as Shipping Zones.

[Documentation](https://en.condless.com/cities-shipping-zones-for-woocommerce/)

= How To Use =
1. Plugin Settings: Select the countries you want to apply the plugin on and save changes (see supported countries map and [Contact](https://en.condless.com/contact/) to request new country/translation).
1. WooCommerce General Settings: Update store location country/state.
1. WooCommerce Shipping Settings: Create shipping zone with the desired locations and its shipping methods and drag it to the top of the list.

= How It Works =
* The title and the values of the built-in state field (which can be used inside shipping zones) will be changed to be as city field, on order creation the original city and state field (if applicable) will be populated based on the selected value.

== Installation ==

= Minimum Requirements =
WordPress 5.2 or greater
PHP 7.0 or greater
WooCommerce 3.4 or greater

= Automatic installation =
1. Go to your Dashboard => Plugins => Add new
1. In the search form write: Condless
1. When the search return the result, click on the Install Now button

= Manual Installation =
1. Download the plugin from this page clicking on the Download button
1. Go to your Dashboard => Plugins => Add new
1. Now select Upload Plugin button
1. Click on Select file button and select the file you just download
1. Click on Install Now button and the Activate Plugin

== Screenshots ==
1. Cities Shipping Zones Plugin Settings
1. WooCommerce Shipping Zones Settings
1. Checkout dropdown city field

== Frequently Asked Questions ==

= How to prevent the state field from beeing displayed in the order address? =

Plugin Settings: disable the 'State Autofill' option.

= Why I can't see the right shipping options on checkout? =

WooCommerce Shipping Zones settings: drag the relevant shipping zone to the top of the list.
Checkout: Verify the country field is present and fill all the address fields.

= Why I can't see the city field on checkout/emails? =

Make sure the country field is present at the checkout page.
If Checkout Fields Manager plugin is installed: Enable the billing/shipping country and state fields, modify the state field label to City.

= How to set the city of the store or users from the dashboard?

Select the city in the state field and write the same in the city field.

= How to create default shipping zone for a country? =

Create shipping zone and select the country itself and drag this shipping zone to be under the shipping zone with the specific cities in the shipping zones list.

= Why the cities dropdown is slow? =

The cities list must be minimized. if it's slow only in the frontend this could be done with the 'Selling Locations' option, otherwise with the 'csz_cities' filter or for states supported countries- 'csz_states' filter.

== Changelog ==

= 1.0.7 - December 22, 2020 =
* Dev - 'woocommerce_states' filter was replaced with 'csz_cities' for the countries the plugin apply on
* Dev - Reconfiguring the shipping zones is required if you applied the plugin on the following countries: Côte d'Ivoire, Kuwait, Latvia, Pakistan, Saint Vincent and the Grenadines, South Africa, Sri Lanka

= 1.0.6 - October 27, 2020 =
* i18n - Supported Countries

= 1.0.5 - July 27, 2020 =
* Enhancement - Distance Fee

= 1.0.4 - June 20, 2020 =
* i18n - Supported Countries

= 1.0.3 - May 31, 2020 =
* Enhancement - Distance Fee

= 1.0.2 - May 5, 2020 =
* Feature - Distance Fee

= 1.0.1 - April 5, 2020 =
* i18n - Supported Countries

= 1.0 - March 5, 2020 =
* Initial release
