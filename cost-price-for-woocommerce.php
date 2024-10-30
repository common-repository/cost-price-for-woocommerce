<?php

	/**
	 * Plugin Name: Cost Price for WooCommerce
	 * Description: This plugin lets the admin calculate the profits they're making on orders and specific products.
	 * Author:      Lewis Self
	 * Author URI:  https://www.lewisself.co.uk
	 * Version:     1.1.0
	 * License:     GPL2+
	 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
	 *
	 * WC requires at least: 3.0.0
	 * WC tested up to: 3.6.4
	 */

	if(!defined('ABSPATH'))
	{
		exit;
	}

	/**
	 * Save the custom fields on product update
	 *
	 * @param	int	$post_id	The product id which is being updated
	 */
	function cpfw_save_custom_fields($post_id)
	{
		update_post_meta($post_id, '_cost_price', sanitize_text_field($_POST['cost_price']));
	}
	add_action('woocommerce_process_product_meta', 'cpfw_save_custom_fields', 20, 1);

	/**
	 * Add the product cost price option to the general product pricing tab
	 */
	function cpfw_stock_notification_emails()
	{
		woocommerce_wp_text_input(array(
			'value'     => get_post_meta($_GET['post'], '_cost_price', true),
			'name'      => 'cost_price',
			'data_type' => 'price',
			'label'     => 'Cost price (' . get_woocommerce_currency_symbol() . ')',
			'id'        => 'cost_price'
		));
	}
	add_action('woocommerce_product_options_pricing', 'cpfw_stock_notification_emails');

	/**
	 * Calculates order profits
	 *
	 * @param		WC_Order	$order	The order which to calculate the profits
	 *
	 * @return	bool | int	The calculated profit. If not possible to calculate, return false
	 */
	function cpfw_calculate_order_profits($order)
	{
		$product_information  = array();
		$order_items          = $order->get_items();
		$total                = 0;
		$cost_price           = 0;
		$cost_price_completed = true;

		foreach($order_items as $product)
		{
			$single_cost_price = get_post_meta($product['product_id'], '_cost_price', true);

			if($single_cost_price != null)
			{
				$total      += $product['total'];
				$cost_price += $single_cost_price * $product['qty'];
			}
			else
			{
				$cost_price_completed = false;

				break;
			}
		}

		if($cost_price_completed)
		{
			return wc_price($total - $cost_price);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Calculate profit margins and save information to order
	 *
	 * @param	int	$order_id	Calculate the profits from the order id
	 */
	function cpfw_completed_order($order_id)
	{
		update_post_meta($order_id, '_order_profit', cpfw_calculate_order_profits(new WC_Order($order_id)));
	}
	add_action('woocommerce_order_status_completed', 'cpfw_completed_order');

	/**
	 * Add profit collumn onto the order information
	 *
	 * @param	int	$order_id	Calculate the profits from the order id
	 */
	function cpfw_order_profit_information($order_id)
	{
		$profits = get_post_meta($order_id, '_order_profit', true);

		if($profits != false) : ?>
			<tr>
				<td class="label">Profits:</td>
				<td width="1%"></td>
				<td class="total"><?php echo $profits; ?></td>
			</tr>
		<?php endif;
	}
	add_action('woocommerce_admin_order_totals_after_tax', 'cpfw_order_profit_information', 10, 1);

	/**
	 * Modify table to include order profit margin
	 *
	 * @param	array			$array	Table array used to generate email order information
	 * @param	WC_Order	$order	The order
	 *
	 * @return	array	The modified table array
	 */
	function cpfw_get_order_item_totals($array, $order)
	{
		$totals = $array['order_total'];

		unset($array['order_total']);

		$array['profits'] = array(
			'label' => 'Profits:',
			'value' => cpfw_calculate_order_profits($order)
		);

		$array['order_total'] = $totals;

		return $array;
	}

	/**
	 * Create new fields for variations
	 */
	function variation_settings_fields($loop, $variation_data, $variation)
	{
		woocommerce_wp_text_input(
			array(
				'id'          => '_cost_price[' . $variation->ID . ']',
				'label'       => 'Variable Cost Price (' . get_woocommerce_currency_symbol() . ')',
				'value'       => get_post_meta($variation->ID, '_cost_price', true),
				'custom_attributes' => array(
					'step'  => 'any',
					'min'   => '0'
				)
			)
		);
	}
	add_action('woocommerce_variation_options_pricing', 'variation_settings_fields', 10, 3);

	/**
	 * Save new fields for variations
	 */
	function save_variation_settings_fields($post_id)
	{
		update_post_meta($post_id, '_cost_price', sanitize_text_field($_POST['_cost_price'][$post_id]));
	}
	add_action('woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2);

	// Add admin dashboard widgets
	require_once('includes/widget.php');

?>
