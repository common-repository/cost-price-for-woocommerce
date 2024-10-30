=== Cost Price for WooCommerce ===

Contributors: lewisself
Tags: Cost, Price, Profits, WooCommerce
Requires at least: 4.0.0
Tested up to: 5.2
Stable tag: Trunk
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

This plugin lets the shop administrator input the cost price for products on their website. Once an order is completed, the admin email gets the profit calculated for the order. This is also displayed on the admin order screen.

== Installation ==

1. Extract "cost-price-for-woocommerce.zip".
2. Upload "cost-price-for-woocommerce" folder via FTP to the WordPress directory: "/wp-content/plugins/".
3. Activate the plugin through the WordPress admin "Plugins" menu.
4. Navigate to the WooCommerce email settings. Click on the "Product Rating Notification Email".
5. Make sure all the appropriate fields are correctly filled in.

== Changelog ==

= 1.0.0 (05/09/2018) =
* Initial Release

= 1.1.0 =
* Removed profits on admin emails
* Fixed bug causing profits being effected when changing cost price on old orders
* Fixed bug when inputting a cost price of 0
* Added support for variable products
* Added admin widget to display products without a cost price