  === Product Code for WooCommerce ===

Contributors: Artiosmedia, westerndeal
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=E7LS2JGFPLTH2
Tags: internal product code, product code, company product number, product id number, product id code, second sku
Requires at least: 3.0.1
Tested up to: 5.6
Version: 1.2.4
Stable tag: 1.2.4
Requires PHP: 5.6.2
License: GPLv3 or later license and included
URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin will allow a user to add a unique internal product identifier in addition to the GTIN, EAN, SKU or UPC throughout the order process. Minor setup, small memory footprint and concise results. As an added bonus, a second optional Product Code field is included, switchable from the settings panel.

== Description ==

This is a user-friendly plugin that many website designers, developers, and business owners look for when they require an additional product code field. It is often used for an inventory control number, internal stock number or a bin location. The plugin allows you to add a product identifier to each or some variable or single items in Woocommerce. The custom field value can be passed through during order fulfillment, referenced from each item ordered. The field value can be viewed user side if desired, or turned off if not.

A unique product code is often added in addition to the GTIN, EAN, SKU and UPC throughout the order process. However, all current plugins that might address this need entail complex setups and functions which result in extra memory usage, system conflicts and frequent updates. This plugin eliminates all those hurdles by providing a simple solution without excessive options.

Simply install, enter your product codes within each product post (variation or single) and publish. Nothing more to it than that! If you don't want customers to be able to see the unique product code, the user side display can be turned off in setup. The field label can be easily changed in setup too, to read ISBN for example or Bin Number, Stock Number, EAN, or JAN. Any value can be create and entered as a single new field.

The added field is compliant to mappable data import and export schemes. This same compliance allows the field to be included in a Google Merchant product feed using the custom mapping. It also provides support for Schema.org/Product with an option to choose the property name (GTIN, EAN, UPC, ISBN) to set inside the structured data. The plugin's languages include: English, Spanish and French.

You can also search product codes using the wordpress default search from the user side as well as from administrator woocommerce product list page on the backend. It is now compatible to search product codes using the popular <a href="https://wordpress.org/plugins/relevanssi/" target="_blank">Relevanssi</a>, <a href="https://searchwp.com/" target="_blank">SearchWP</a> and, <a href="https://ajaxsearchpro.com/" target="_blank">Ajax Search Pro</a>.

== Installation ==

1. Upload the plugin files to the '/wp-content/plugins/plugin-name' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Enter the Product Code under the SKU from either Variable or Simple products.

== Technical Details for Release 1.2.4 ==

Load time: 0.309 s; Memory usage: 2.85 MiB
PHP up to tested version: 7.4.8
MySQL up to tested version: 8.0.21
cURL up to tested version: 7.66.0, OpenSSL/1.0.2u

== Using in Multisite Installation ==

1. Extract the zip file contents in the wp-content/mu-plugins/ directory of your WordPress installation. (This is not created by default. You must create it in the wp-content folder.) The 'mu' does not stand for multi-user like it did for WPMU, it stands for 'must-use' as any code placed in that folder will run without needing to be activated.
2. Access the Plugins settings panel named 'Product Code for WooCommerce' under options.
3. Enter the Product Code under the SKU from either Variable or Simple products.

== Configuration with Relevanssi plugin ==

1. Open up Indexing tab from Settings->Relevanssi page.
2. From the Post Type select "Product" and "Product Variation".
3. From the Custom fields dropdown select "Some" and add custom fields "_product_code" and "_product_code_second" and save.
4. Move to Searching tab and unselect checkbox "Respect exclude_from_search" and save.
5. Access Indexing tab, click button "Build the Index" and save.

== Configuration with SearchWP plugin ==

1. SearchWP requires the SearchWP WooCommerce Integration addon.
2. Open up Settings Tab from Settings->SearchWP page.
3. Add post type "Product" if not added by clicking "Add Post Type".
4. Click "Add Attributes", select "Custom Fields" and add "_product_code" and "_product_code_second" fields from the dropdown box. Move slider to right on both toward "Max".
5. Lastly click "Save Engines" and then click "Rebuild Index".

== Configuration with Ajax Search Pro plugin ==

1. Open up "Ajax Search Pro" settings page via admin menu.
2. Create/Edit the search instance.
3. Add "Products[product]" and "Variation[product_variation]" from the post types list.
4. Add "_product_code" and "_product_code_second" fields from the custom fields list and save.
5. If you have selected "Index table engine" for the search engine then index it again.

== Frequently Asked Questions ==

= Is this plugin frequently updated to Wordpress compliance? =
Yes, attention is given on a staged installation with many other plugins via debug mode.

= Is the plugin as simple to use as it looks? =
Yes. No other plugin exists that adds an additional custom product code so simply.

= Has there ever any compatibility issues? =
To date, none have ever been reported.

= Can the custom Product Code field be fed to Google Merchant? =
We can't possibly assure compatibility with every feed manager, but the properly built ones find the field correctly. We suggest using YITH Google Product Feed for WooCommerce Premium. The custom field appears as 'Product Id [id]' right on top of the custom field selections.

= How do I export the Product Code field from WooCommerce? =
We use WP All Export Pro by Soflyy which works great, but so does the free Advanced Order Export For WooCommerce By AlgolPlus.
- Click "Export Orders" under WooCommerce.
- Click to open "Set up fields to export"
- On the right click "Products"
- The field "[P] Product Id" is listed as the field to export from this plugin.

= Can I rename the Product Code field to another title? =
Previously, the function.php required a snippet addition to do so. As of version 1.0.6, in the settings panel you will find an option to edit the field title with a limit of 18 characters including spaces. Whatever title is entered will change on the user side and admin side and throughout the order process.

= Is there short code that allows inserting product code? =
The shortcode to show the Product code is `[pcfw_display_product_code]` you can use these attributes:

• `id` the product id
• `pc_label` the product code label that will be displayed before the code. By default is "Product Code:", but this can be changed inside the settings panel.
• `pcs_label` the product code second label that will be displayed before the code. By default is "Product Code Second:", but this can be changed inside the settings panel.
• `wrapper` you can wrap the label and product code in div or span. By default is 'div' for the shop page and 'span' on the other pages.
• `wrapper_code` the container of product code. By default is a 'span'.
• `class` the class of wrapper container. By default is 'pcfw_code'.
• `class_wrapper` the class of wrapper code container. By default is 'pcfw_code_wrapper'.

Note: For variable product you need to pass variation product id.

= Is the code in the plugin proven stable? =
Please click the following link to check the current stability of this plugin:
<a href="https://plugintests.com/plugins/product-code-for-woocommerce/latest" rel="nofollow ugc">https://plugintests.com/plugins/product-code-for-woocommerce/latest</a>

== Screenshots ==

1. The Product Code as found in a Simple Product
2. The Product Code as found in a Variable Product
3. The Product Code appears under the SKU on the user side
4. The Product Code appears below the description in the shopping cart
5. The Product Code appears below the SKU and Variation ID on the order page
6. The plugin's limited selection function settings panel

== Upgrade Notice ==

None to report as of the release version

== Changelog ==

1.2.4 12/31/20
- Fixed export of products with product code meta fields.
- Added setting to apply structure data property for product code.
- Added 'N/A' for the structured data if product code is not set for any product.
- Added shortcode [pcfw_display_product_code] to display product code on single product and custom pages.
- Remove useless Import/Export Settings option

1.2.3 12/13/20
- Fixed variation search for search plugins.
- Fixed search of product using product code at backend with search plugins.
- Fixed not displaying of product code for variation product.
- Fixed displaying of warning banner with new installs.
- Fixed database upgrade script to be run after version 1.1.0.
- Fixed displaying product code at cart, checkout, and receipt.
- Added support for Schema.org/Product with an option to choose the property name to set inside the structured data

1.2.2 10/21/20
- Recompile to solve fatal errors

1.2.1 10/20/20
- Reload export module due to error

== Changelog ==
1.2.0 10/17/20
- Add second product code option
- Add script to merged two product meta values into single
- Add switch to hide user side field when the value is empty
- Update language files to include new fields
- Update sample screens to show new fields
- Assure compliance with WooCommerce 4.6.0
- Add Import/Export Settings option 

1.1.0 05/15/20
- Fixed search error on administrator product list page
- Updates for Wordpress 5.4.1
- Assure compliance with WooCommerce 4.1.0

1.0.9 04/26/20
- Add search module for Relevanssi search

1.0.8 04/02/20
- Add search compliance including third party plugins
- Add search module for SearchWP search
- Add search module for Ajax Search Pro search
- Updates for Wordpress 5.4
- Assure compliance with WooCommerce 4.0.1

1.0.7 02/02/20
- Updates for Wordpress 5.3.2
- Assure compliance with WooCommerce 3.9.2
- Fix missing Product Code label upon install

1.0.6 12/11/19
- Updates for Wordpress 5.3
- Assure compliance with WooCommerce 3.8.1
- Remove composer.json dependencies
- Add submenu access to setup
- Add ability to edit the field title
- Overall composition and text edits
- Fix language POTS not loading

1.0.5 11/09/19
- Updates for Wordpress 5.2.4
- Modifications for WooCommerce 3.8.0
- Support for WooCommerce Admin 0.21.0
- Tested Compatible with WPML
- Adjust for WooCommerce API REST
- Current version support updated

1.0.4 08/15/19
- Updates for Wordpress 5.2.2
- Modifications for WooCommerce 3.7.0
- Current version support updated

1.0.3 05/05/19
- Updates for Wordpress 5.2
- Assure compliance for WooCommerce 3.6.2
- Current version support updated

1.0.2 04/03/19
- Update to allow Wordpress search of Product Code fields
- Assure compliance for WooCommerce 3.5.7
- Current version support updated

1.0.1 02/01/19
- Fix bug that caused code duplication in some variations
- Current version support updated

1.0.0 01/15/19
- Initial release