=== MStore API ===
Contributors:      InspireUI Ltd
Tags:              mstore, fluxstore, react-native, flutter, inspireui, ios, android
Requires at least: 4.4
Tested up to:      5.6
Stable tag:        2.0.6
License:           GPL-2.0
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

The plugin is used for config the Mstore/FluxStore mobile and support RestAPI to connect to the app.

== Description ==

The plugin is used for config the Mstore/FluxStore mobile and support RestAPI to connect to the app.

[youtube https://youtu.be/sYnHhnS5WnQ]

Fluxstore is a universal e-commerce app inspired by Flutter framework, made by Google. With the mission of reducing thousands of hours of business spent on designing, developing and testing a mobile app, Fluxstore comes as a complete solution for optimizing to deliver your app to the market with high productivity and cost efficiency. It could be able to satisfy all of the business requirements including e-commerce functionalities, impressive UX design and smooth performance on both iOS and Android devices.

If your business has already had the website that built based on WooCommerce, Magento or Opencart, then it is easy to integrate with Fluxstore by just a few steps, and quickly release the final app to both Appstore and Google Play store. The download package is included the full source code and many related resources (designs, documents, videos…) that help you install in the smoothest way.

Either you are business people with raising sale ambition or developers with faster mobile application creation need, Fluxstore provides you solutions.
Faster- Smoother- Closer. 

### Reference links
- Company Website: [https://inspireui.com](https://inspireui.com)
- App demo: [iOS](https://apps.apple.com/us/app/mstore-flutter/id1469772800), [Android](https://play.google.com/store/apps/details?id=com.inspireui.fluxstore)
- Youtube Channel: [https://www.youtube.com/inspireui](https://www.youtube.com/inspireui)
- Document: [https://docs.inspireui.com](https://docs.inspireui.com)
- MStore website: [https://mstore.io](https://mstore.io)
- Fluxstore website: [https://fluxstore.app](https://fluxstore.app)

== Installation ==

= Manual Installation =

1. Upload the entire `/mstore-api` directory to the `/wp-content/plugins/` directory.
1. Activate 'MStore API' through the 'Plugins' menu in WordPress.

= Better Installation =

1. Go to Plugins > Add New in your WordPress admin and search for 'MStore API'.
1. Click Install.

== Changelog ==
= 3.1.4 =
  * Sort shop orders desc for WCFM
  * Fix duplicate orders issue in FluxStore Manager

= 3.1.3 =
  * Support get stores list based on current location for WCFM and Dokan

= 3.1.2 =
  * Support wc/v3/flutter version 3 for vendor api to use in MStore Dokan.

= 3.1.1 =
  * Fix crash when get products

= 3.1.0 =
  * Allow to edit push notificaion message when order status changed

= 3.0.9 =
  * Fix banner image in stores api

= 3.0.8 =
  * Support Product Add On

= 3.0.7 =
  * Fix crash order api

= 3.0.6 =
  * Remove draft products in home cache api
  
= 3.0.5 =
  * Add endpoints for FluxStore admin (compatible with WCFM plugin)
  * Add endpoint for finding nearby stores based on user location (compatible with WCFM plugin)
  * Fix get stores api

= 3.0.4 =
  * Add reset password api

= 3.0.3 =
  * Add product data to line_items in orders list api

= 3.0.2 =
  * Remove meta_data product for home cache api

= 3.0.1 =
  * Send notification to vendor as new orders

= 3.0.0 =
  * Send notification to user when order status changed

= 2.9.9 =
  * Support widgets cache
  * Decode cookie in GET request

= 2.9.8 =
  * Support open vendor admin in Fluxstore
  * Fix shipping address form doesn't show in checkout page

= 2.9.7 =
  * Fix undefined constant __return_true in old wordpress version

= 2.9.6 =
  * Support Points and Rewards for WooCommerce
  * Fix showing order note for one page checkout

= 2.9.5 =
  * Fix apple login issue
  * Fix one page checkout with product variantion
  * Sync cart from mobile to website
  * Get taxes api
  * Fix search store for WCFM
  * Allow to setting limit per page

= 2.9.4 =
  * Support Fluxstore version 1.7.6

= 2.0.0 =
  * Major update to remove the depend on JSON API plugins
  * Add category caching API
  * Fix security issues

= 1.4.0 =
  * Update Caching

= 1.3.0 =
  * Add firebase phone auth

= 1.2.0 =
  * Support FluxStore
  * Update SMS Firebase Login

= 1.0.0 =
  * First Release
  * Support Mstore App
