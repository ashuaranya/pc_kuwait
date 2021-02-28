=== WooCommerce Products Wizard ===
Contributors: troll_winner@mail.ru
Tags: filter, products, step-by-step, wizard, multi-step

This plugin helps you sell your products by the step-by-step wizard. Use the [woocommerce-products-wizard] shortcode to init.

== Description ==

This plugin helps you sell your products by the step-by-step wizard.

= Features include: =

* Control the product or product variation visibility by AND/OR conditions
* Choose which terms will used in the wizard and set their order
* Control the each tab description text and placement
* Single or several products from the each term availability
* Different templates to view products and product variations
* Customizing templates by making the copy in the theme directory
* Option to display the first tab and its content

== Installation ==

1. Install WooCommerce Products Wizard by uploading the files to your server
2. Be sure WooCommerce Plugin is enabled
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the WooCommerce -> Products Wizard -> Add new.
5. Create required steps number and setup other options.
6. Save the post.
7. Use the shortcode to init the plugin.

== Changelog ==

= 9.1.3 =
New: Buttons nav list view
Tweak: Widget toggle control badge
Fix: Redirect to a wizard feature with AJAX add to cart action
Fix: Product category settings saving bug

= 9.1.2 =
Fix: Latest WooCommerce version combined kits bug
Tweak: Code improvements

= 9.1.1 =
New: Availability rules by product attributes
Fix: "Scrolling to top on the form update" bug

= 9.1.0 =
New: "Send current state hash via AJAX" global setting
New: Step thumbnail settings
New: "Add to cart" button behavior value
Tweak: Code improvements
Fix: Results table mobile view
Fix: Mobile navigation work

= 9.0.0 =
Removed: All dependencies/exclusions settings
New: Unified availability rules setting for steps/categories/products/variations
New: File type support for custom inputs
New: Price discount type setting
Fix: Description step content tags wrapping
Fix: Better variations work
Fix: Better cart reflection work
Fix: "Add to main cart" button work

= 8.9.0 =
New: Thumbnail generation feature
New: Filters include/exclude terms setting
Fix: Bug of the emails sent
Tweak: Admin copy step possibility
Tweak: Code improvements

= 8.8.0 =
New: "Select several variations per product" setting
New: "Show steps names" setting
New: On/off sticky elements settings

= 8.7.0 =
New: Attach wizard to a product feature
Fix: Double discount rare bug
Fix: Products reflect in the main cart bugs

= 8.6.0 =
New: "Show steps names in cart" setting
New: Sidebar position setting
New: None navigation view
New: Categories dependencies setting
Fix: Inline custom fields work bug

= 8.5.0 =
New: Class settings for each main control button
New: Step id setting for redirect to a wizard feature
New: Min/max total products price setting
New: "Results tab description" setting
New: "Show results tab table" setting
New: More values for "Show sidebar" setting
Fix: Unmet product variations bug
Fix: "Hide prices" option with table view bug
Fix: Description tab content tags wrapping
Fix: Hidden product quantity work bug
Tweak: Total price including/excluding taxes
Tweak: Item description output for the table view
Tweak: Documentation update

= 8.4.0 =
New: "Add to cart by quantity" step setting
New: Form item title/price font size settings
New: "None" value for "Item description source" setting
Fix: Filter by categories bug
Fix: Filter inline-checkbox/radio views bug
Fix: Rear bugs

= 8.3.0 =
New: Nav action setting
New: "Products per page items" step setting
New: "Enable 'Order by' dropdown" step setting
New: "Filter position" step setting
New: More filter views
Tweak: Small code improvements

= 8.2.1 =
Fix: Icons bug with the custom styles file
Fix: Bootstrap 3/4 cross CSS grid bug
Tweak: Documentation update

= 8.2.0 =
Removed: Variable products without default selected variation
New: Admin custom styles settings
New: Pre-defined kit base product setting
New: Product tags output setting
New: Output product title link setting
New: "Don't add specific products to the cart" setting
New: "All items are selected by default" setting
Tweak: Hide out of stock items using product attribute filter
Tweak: "Selected items by default" setting variations support
Tweak: "Excluded products" setting variations support
Tweak: noUiSlider update
Tweak: Admin improvements and fixes
Fix: Products filter by category work bug
Fix: Individual cart controls bugs
Fix: "Don't add to cart" setting within kits using
Fix: Price range filter values bug
Fix: Bulk edit bug

= 8.1.0 =
New: "Hide choose element" step setting
Tweak: Clickable thumbnail of "type 5" item template
Fix: Drop steps on disable/enable the plugin
Fix: Rare PDF generation bug

= 8.0.0 =
Removed: Subtotal and discount rows from widget and results
New: Unlimited steps with any products within instead of just categories
New: Custom inputs in steps description
New: "Exclude already added products of steps" setting
New: "Merge thumbnail with gallery" step setting
New: "Kit base price" setting
New: Products/categories price discount rules
New: Product item view type 6
Tweak: Minimum/Maximum products selected/quantity rules based on one or multiple steps
Fix: Small admin improvements
Fix: Line nav view bug

= 7.0.0 =
Removed: "Enable all tabs availability" setting
Removed: "Enable single step mode" setting
New: "Mode" setting and new "sequence" mode
New: "Expand filter by default" step setting
Fix: "Store session in the DB" global setting initialization bug
Fix: Variation data pass bug
Fix: Combined kits quantity bug
Tweak: Code optimization

= 6.0.0 =
Tweak: Global API refactoring
Tweak: Better nav UI
New: Form item view with modal
New: Nav template setting and a few new templates
Fix: Rare product variation bugs

= 5.1.0 =
New: Images variations view
Fix: Multiple variation properties bug
Fix: Results PDF total pages bug
Fix: Minimum/Maximum total products quantity among pages bug

= 5.0.0 =
Removed: "Include full styles file" global setting
New: "Styles including type" global setting
New: "Store session in the DB" global setting
New: Filtering by a search string
New: "Excluded added products" term setting
New: "Kit price" for combined kits setting
Fix: Pagination with filters bug and among pages cart bug
Fix: Cross-terms filtering results bug
Tweak: Filter section toggling
Tweak: Code optimization and fixing

= 4.5.0 =
New: Results emailing settings
New: Buttons variations view
Fix: Sticky elements rare bugs
Fix: Extra Product Options with variable products init bug

= 4.4.0 =
Tweak: Migrate to Bootstrap v4
Tweak: Styles refactoring
Fix: "Skip" button appearance bug

= 4.3.0 =
New: Minimum/Maximum products selected setting
New: Minimum/Maximum total products quantity setting
New: Default product quantity setting
New: Image-radio/checkbox filter views
Tweak: Form notices and messages refactoring

= 4.2.3 =
Tweak: Code refactoring
Fix: Steps dependencies bug
Fix: Subscriptions fees pricing bug

= 4.2.2 =
Tweak: Performance optimization
Fix: Prematurely published table layout bugs

= 4.2.1 =
Tweak: Small code improvements
Fix: Subscription products choose bug

= 4.2.0 =
New: Discount setting
Fix: Extra Product Options with variable products bug

= 4.1.0 =
New: Selected items by default setting
New: Products exclusions setting
Tweak: noUiSlider update
Tweak: More reliable description editor in the admin part

= 4.0.0 =
Fix: Styles bugs
Fix: Stock bug with combined kits
Tweak: Code refactoring

= 3.21.0 =
New: "Add to cart" button behavior setting
New: More options for text strings
Fix: "Hide prices" option bug
Tweak: Templates improvements
Tweak: Code refactoring

= 3.20.2 =
Fix: Min products quantity check on product remove bug
Fix: Steps settings save bug

= 3.20.1 =
Tweak: Deep code refactoring
Fix: Min products quantity check on product remove
Fix: Max products quantity bug with the stock limit value

= 3.20.0 =
New: Edit cart item button
Tweak: Admin part updates
Tweak: Documentation update
Fix: Lost "remove cart item text" option bug

= 3.19.0 =
New: Min/max products quantity setting by another step's products in the cart
New: Min/max total products quantity setting
Tweak: Documentation update
Fix: Individual controls work bugs

= 3.18.0 =
New: Min/max selected products setting by another step's products in the cart
Fix: After product remove bug
Fix: Min/max selected products calculation bug
Removed: Deprecated numeric grid layouts and excess template settings

= 3.17.0 =
Fix: Individual controls in the single step mode bugs
New: Bulk dependencies edit
New: "Hide prices" option
Tweak: Code refactoring

= 3.16.0 =
New: Redirect to the wizard on product add to WC cart option
Fix: Tabs order bug with WC v3.6.1
Fix: EPO plugin files upload better support

= 3.15.1 =
Tweak: 4.8.4 EPO plugin version support
Tweak: Better cart reflections work

= 3.15.0 =
New: Combining products in the kits option
New: Order products by price
Fix: Single step layout widget bug
Fix: Losing products from the other pages bug

= 3.14.0 =
New: Bootstrap 4 framework support
New: Product "Update" button
New: "Don't add products from this step to WooCommerce cart" option
Fix: "Extra Product Options" bug with validation and conditions
Fix: Filter reset on adding/removing a product
Tweak: Widget position on mobile
Tweak: Documentation update

= 3.13.0 =
New: "Item description source" setting
New: Filtering by an attribute
New: Filters refactoring
New: Filter select view
Fix: Product dependencies bug
Tweak: Code refactoring

= 3.12.2 =
Fix: "Extra Product Options" default values bug
Tweak: Code refactoring

= 3.12.1 =
Fix: Single step products order bug
Fix: "Extra Product Options" bug with the kits
Fix: Old IE bugs
Tweak: Code refactoring

= 3.12.0 =
New: Maximum products selected option
New: Quantity change for the kits
Fix: Products reflection on the main cart bug
Tweak: RTL support
Tweak: Code refactoring

= 3.11.2 =
Fix: "No selected items by default" with "Minimum products selected" bug
Tweak: Code refactoring
Tweak: Translates update

= 3.11.1 =
Tweak: Code refactoring
Tweak: Scripts including control

= 3.11.0 =
New: Products grouping in the cart setting
Fix: Long pagination bug
Fix: Insignificant code bugs

= 3.10.0 =
New: Responsive grid columns settings and customizable grid layout
Fix: Empty filters bug

= 3.9.1 =
Fix: HTML validation bugs
Fix: Bug default Woocommerce scripts influence
Fix: Single step mode work bug
Fix: Variations availability bug
Tweak: "Reset" button availability in a single step mode
Tweak: Dependencies using refactoring
Tweak: Results table widget refactoring

= 3.9.0 =
New: Gallery column setting
New: Products order setting

= 3.8.1 =
New: Get wizard APIs
Fix: Variation availability bug
Fix: No-JS version submit bug
Fix: Product stock data output

= 3.8.0 =
New: Product stock data output
Fix: Missed wNumb script

= 3.7.0 =
New: "Reflect products in the main cart immediately" option
Tweak: Step description visual editor

= 3.6.1 =
Fix: "Minimum products selected" setting work

= 3.6.0 =
New: More message settings
Fix: Small code bugs

= 3.5.0 =
New: "Scrolling up on the form update" option
New: Messages options
Tweak: Admin settings grouping
Tweak: Code refactoring

= 3.4.1 =
New: Norwegian translate by Jan Arne Bakke
Tweak: Variation attribute views refactoring
Tweak: Deep views refactoring

= 3.4.0 =
New: Filtering products
New: "Clear WooCommerce cart on confirms" setting
Tweak: Views refactoring
Fix: Admin Select2 rare bug

= 3.3.2 =
Fix: PrettyPhoto plugin re-init bug
Tweak: Table responsive script launch better reliability
Tweak: Documentation update

= 3.3.1 =
Fix: Variable product work without "Extra Product Options" plugin

= 3.3.0 =
New: "Minimum products selected" setting
New: French translation by Louis Houde
Tweak: "Extra Product Options" plugin variable products support
Tweak: "Extra Product Options" plugin support in single-step mode
Tweak: Views refactoring

= 3.2.0 =
New: "Show sidebar" option
Fix: Results table empty column
Fix: Add to cart occasional bug with 3rd party plugins versions

= 3.1.1 =
Fix: Rare redirect bugs fix
Fix: "Extra Product Options" plugin initialization bug

= 3.1.0 =
New: "Final redirect url" setting

= 3.0.0 =
New: Minimum/maximum product quantity options
New: Enable/disable results tab option
New: Styles update
Removed: "Minimum products to add" option
Tweak: Deep refactoring

= 2.14.1 =
Fix: Critical output bug

= 2.14.0 =
New: Supporting of "Extra Product Options" plugin
Tweak: Views refactoring
Fix: Form layout wrong items

= 2.13.0 =
New: Sticky controls
Tweak: Better responsibility
Fix: Wrong products order

= 2.12.2 =
New: Sticky sidebar top offset setting
Fix: Pagination rare error
Tweak: Code refactoring

= 2.12.1 =
Fix: WooCommerce 3.2.x version bug
Fix: Rare plugin activation bug

= 2.12.0 =
New: Default cart content
Fix: Add to main cart bug

= 2.11.3 =
Fix: Form templates selection bug
Fix: Products quantity reset on submit
Fix: Form submit validation bug

= 2.11.2 =
Tweak: Images lightbox better support

= 2.11.1 =
Tweak: Code refactoring

= 2.11.0 =
New: Pagination setting
New: "Always show sidebar" setting
Tweak: Styles update
Tweak: Deep code refactoring

= 2.10.5 =
Fix: PHP namespaces bugs

= 2.10.4 =
Fix: WooCommerce v3.1.0 bug with variation description output

= 2.10.3 =
Fix: WooCommerce v3.0.9 bug with variable products titles

= 2.10.2 =
Tweak: Product views responsibility improvements
Tweak: Code refactoring
Fix: Missing description field in the term modal

= 2.10.1 =
Fix: Minimum items to add bugs

= 2.10.0 =
New: "Required added products" tabs setting
Fix: Styles bugs

= 2.9.0 =
New: "Remove" button in the widget and results table setting
New: Excluded term products setting
New: "Products included" and "order" setting
New: Gulp build
Tweak: Results responsibility improved
Removed: Src LESS styles

= 2.8.1 =
New: Admin sortable lists
Fix: Admin multiply dependencies bug with WooCommerce v3

= 2.8.0 =
New: Product variation description output
Fix: Save dependencies with WooCommerce v3
Fix: Work bugs

= 2.7.0 =
New: Sticky widget
New: Single step mode
Tweak: Assets refactoring
Fix: WooCommerce v3 admin dependencies bugs

= 2.6.0 =
Tweak: WooCommerce v3 support
Tweak: No-js version improved
Tweak: Remove deprecated code
Tweak: Code refactoring

= 2.5.1 =
Tweak: jQuery v3 support
Fix: Admin term remove bug

= 2.5.0 =
New: "No selected items by default" setting
New: "Enable all tabs availability" setting
Fix: Wizard admin page errors

= 2.4.1 =
Fix: Save variation fields for WooCommerce version 2.4.4 and upper
Tweak: Improved variations dependencies work

= 2.4.0 =
New: "Disable dependencies" option
Fix: Main cart product quantity overwrite
Tweak: Code refactoring

= 2.3.1 =
Fixed: Back-button appearing
Fixed: Admin step settings modal styles
Fixed: Deep code refactoring

= 2.3.0 =
New: Individual add-to/remove-from cart button
New: Step title setting
Fix: Typo "skipFrom" to "skipForm"
Fix: Few tiny errors

= 2.2.0 =
Tweak: Admin settings for the text of the control
Tweak: Better terms tree in admin
Fix: Scroll to unchecked variation attributes

= 2.1.1 =
Fix: Products galleries lightbox separation
Fix: Add-to-cart button using in IE

= 2.1.0 =
New: Added item template setting
New: Added new item views
New: Added images gallery view
Tweak: Widget view update

= 2.0.3 =
Fix: Define admin ajax-url

= 2.0.2 =
Tweak: Translate update

= 2.0.1 =
Fix: "Can't use method return value in write context" in "router.php"
Fix: Results variable cart item thumbnail fixed
Fix: Cart total fix

= 2.0.0 =
Tweak: New engine v2
Tweak: Added multiple wizards support
Tweak: Added "minimum selected items to add" option

= 1.0.9 =
Fix: Fixed product variation price html
Fix: Enable sub-categories using

= 1.0.8 =
Fix: Minified full CSS fix
Fix: Fixed cart total float value
Fix: Fixed variable products work

= 1.0.7 =
Tweak: Added "back" and "reset" buttons
Tweak: Added buttons admin enable/disable
Tweak: Added widget and results images lightbox support
Tweak: Update Polish translate
Tweak: LESS/SCSS/CSS update
Tweak: Included base styles file with admin option
Fix: Item variations views class fix

= 1.0.6 =
Fix: Shortcode output fix
Tweak: Added Polish translate by Piotr Główka <biuro@z3.com.pl>

= 1.0.5 =
Tweak: Using SCRIPT_DEBUG constant
Tweak: Added the WooCommerce default lightbox support
Tweak: Added translates support
Tweak: Added Russian translate

= 1.0.4 =
Tweak: Documentation update to 1.0.1
Fix: Next active tab class fix

= 1.0.3 =
Fix: Tabs item classes fix
Fix: Form item thumbnail image class fix
Fix: Change the item thumbnail srcset if product variation with an image founded
Fix: Subtotal output space
Tweak: Adding variation types admin filter
Tweak: Scripts and styles enqueue version by constant
Tweak: Simplify WC->addProductToMainCart method
Tweak: Update templates structure
Tweak: LESS/SCSS/CSS update

= 1.0.2 =
Tweak: Changed spinner file format from "GIF" to "SVG"
Tweak: PHP code style
Tweak: JS code style
Tweak: Clean and optimize js code
Tweak: HTML output is secured by the escaping functions
Tweak: Added messages views directory
Tweak: Added JSDoc comments
Tweak: Added filters to some methods outputs
Tweak: Added js modular structure
Tweak: Improved not filled variable products handler
Tweak: Move work logic in the class methods
Tweak: More OOP features
Fix: Tabs items initial classes fix
New: Added SCSS-source file

= 1.0.1 =
Tweak: Added PHPDoc comments to the class-files
Tweak: Added js events triggers
Tweak: Improved no-js plugin work
Fix: Fixed variable products dependency work
Fix: Fixed the group argument work

= 1.0.0 =
Initial release
