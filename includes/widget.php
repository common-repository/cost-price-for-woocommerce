<?php

	// This file is a mess. Clean up the code soon

	/**
	 * Create dashboard widget to display profits for each product
	 */
	function cpfw_create_profit_dashboard_widgets()
	{
		wp_add_dashboard_widget(
			'woocommerce_product_profit_margins',
			'Product Profit Margins',
			'cpfw_create_product_profit_margins_dashboard_widget_html'
		);
	}
	add_action('wp_dashboard_setup', 'cpfw_create_profit_dashboard_widgets');

	/**
	 * Take cost price and regular price and calculate the font colour
	 */
	function cpfw_calculate_font_color($cost_price, $price)
	{
		if(($price - $cost_price) > 0)
		{
			$font_color = 'green';
		}
		elseif(($price - $cost_price) == 0)
		{
			$font_color = 'orange';
		}
		else
		{
			$font_color = 'red';
		}

		return $font_color;
	}

	/**
	 * Dashboard product profit widget HTML
	 */
	function cpfw_create_product_profit_margins_dashboard_widget_html()
	{
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1
		);

		$loop = new WP_Query($args);

		if($loop->have_posts())
		{
			$product_prices = array();

			echo '<table style="width:100%;">';
			echo '<tr>';
			echo '<th style="text-align:left;">Product</th>';
			echo '<th style="text-align:right;">Cost Price</th>';
			echo '<th style="text-align:right;">Product Price</th>';
			echo '<th style="text-align:right;">Profit</th>';
			echo '</tr>';

			while($loop->have_posts())
			{
				$loop->the_post();

				global $product;

				if(!$product->is_type('variable'))
				{
					$product_cost_price = get_post_meta($product->get_id(), '_cost_price', true);

					if($product_cost_price != null)
					{
						$product_price  = $product->get_price();

						echo '<tr>';
						echo '<td><a href="' . get_edit_post_link() . '"> ' . get_the_title() . '</a></td>';
						echo '<td style="text-align: right;">' . wc_price($product_cost_price) . '</td>';
						echo '<td style="text-align: right;">' . wc_price($product_price) . '</td>';
						echo '<td style="text-align: right; color:' . cpfw_calculate_font_color($product_cost_price, $product_price) . ';">' . wc_price($product_price - $product_cost_price) . '</td>';
						echo '</tr>';

						$product_prices[] = array(
							'price'      => $product_price,
							'cost_price' => $product_cost_price
						);
					}
				}
				else
				{
					$variation = '';
					$variation_count = 0;

					foreach($product->get_available_variations() as $key => $value)
					{
						$variation_cost_price = get_post_meta($value['variation_id'], '_cost_price', true);
						$variation_price      = get_post_meta($value['variation_id'], '_price', true);

						if($variation_count == 0)
						{
							echo '<tr><td><b><a href="' . get_edit_post_link() . '"> ' . get_the_title() . '</a></b></td><td></td><td></td><td></td></tr>';
						}

						if($variation_cost_price != null)
						{
							echo '<tr>';
							echo '<td><small>' . $value['sku'] . '</small></td>';
							echo '<td style="text-align: right;">' . wc_price($variation_cost_price) . '</td>';
							echo '<td style="text-align: right;">' . wc_price($variation_price) . '</td>';

							echo '<td style="text-align: right; color:' . cpfw_calculate_font_color($variation_cost_price, $variation_price) . ';">' . wc_price($variation_price - $variation_cost_price) . '</td>';
							echo '</tr>';

							$product_prices[] = array(
								'price'      => $product_price,
								'cost_price' => $variation_cost_price
							);

							$variation_count++;
						}
					}
				}
			}

			if($product_prices)
			{
				$total_cost_price = 0;
				$total_price      = 0;
				$total_count      = 0;

				foreach($product_prices as $product_price)
				{
					$total_cost_price += $product_price['cost_price'];
					$total_price      += $product_price['price'];

					$total_count++;
				}

				echo '<tr style="background-color:#333; height:1px;"><td colspan="4"></td></tr>';
				echo '<tr>';
				echo '<td><b>Average:</b> </td>';
				echo '<td style="text-align:right;"><b>' . wc_price($total_cost_price / $total_count) . '</b></td>';
				echo '<td style="text-align:right;"><b>' . wc_price($total_price / $total_count) . '</b></td>';
				echo '<td style="text-align:right;"><b>' . wc_price(($total_price - $total_cost_price) / $total_count) . '</b></td>';
				echo '</tr>';
			}

			echo '</table><br />';

			echo '<table>';
			echo '<tr><th colspan="3" style="text-align:left;">Missing Cost Price</th></tr>';

			$missing_cost_price_products = 0;

			while($loop->have_posts())
			{
				$loop->the_post();

				global $product;

				if(!$product->is_type('variable'))
				{
					$product_cost_price = get_post_meta($product->get_id(), '_cost_price', true);

					if($product_cost_price == null)
					{
						echo '<tr>';
						echo '<td><a href="' . get_edit_post_link() . '"> ' . get_the_title() . '</a></td>';
						echo '</tr>';

						$missing_cost_price_products++;
					}
				}
				else
				{
					$variation = '';

					foreach($product->get_available_variations() as $key => $value)
					{
						$variation_cost_price =  get_post_meta($value['variation_id'], '_cost_price', true);

						if($variation_cost_price == null)
						{
							$variation .= '<br/><span>' . $value['sku'] . '</span>';

							$missing_cost_price_products++;
						}
					}

					if($variation)
					{
						echo '<tr><td>';

						echo '<a href="' . get_edit_post_link() . '"> ' . get_the_title() . '</a>' . $variation;

						echo '</td></tr>';
					}
				}
			}

			if($missing_cost_price_products == 0)
			{
				echo '<tr><td colspan="3">All products have cost prices.</td></tr>';
			}

			echo '</table>';
		}

		wp_reset_query();
	}

?>
