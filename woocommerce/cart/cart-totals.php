<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     2.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<style type="text/css">
	.pincode{
		width: 100%;
	    border: 2px solid #acacac;
	    border-radius: 2px;
	    padding: 8px 10px;
	}
	label.not_avil_msg {
	    color: red;
	}
	.check_pincode{
		width: 100%;
	    text-align: center;
	    padding: 0px 0 !important;
	    margin-top: 10px !important;
	}
	.blockUI.blockOverlay:before {
		height: 1em;
		width: 1em;
		display: block;
		position: absolute;
		top: 50%;
		left: 50%;
		margin-left: -0.5em;
		margin-top: -0.5em;
		content: '';
		animation: spin 1s ease-in-out infinite;
		background: url('http://tajinstruments.in/wp-content/plugins/woocommerce-pincode-price/woocommerce/assets/images/loader.svg') center center;
		background-size: cover;
		line-height: 1;
		text-align: center;
		font-size: 2em;
		color: rgba(#000, 0.75);
	}
</style>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2><?php _e( 'Cart totals', 'woocommerce' ); ?></h2>

	<table cellspacing="0" class="shop_table shop_table_responsive">

		<tr class="cart-subtotal">
			<th><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<tr class="pincode_box">
			<th><?php _e( 'Check Pincode', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Check Pincode', 'woocommerce' ); ?>">
				<form method="post">
					<input type="number" name="pincode" class="pincode input-text" value="<?php echo ( isset( $_SESSION['shipping_pincode'] ) ) ? $_SESSION['shipping_pincode'] : '' ; ?>">
					<?php if(isset($_SESSION['pincode_avil_msg']) && $_SESSION['pincode_avil_msg'] != ''){ 

							if ($_SESSION['pincode_avil_msg'] == 'success_msg') { ?>
								<label class="avil_msg" style="color: green;">
									Shipping is available for your area, you can Proceed to Checkout.
								</label>
					<?php   }else{ ?>
								<label class="not_avil_msg">
									Sorry for the inconvenience. To complete your order please call us +91-9328511235 or E-mail Us <a href="mailto:sales@tajinstruments.in" title="mailto:sales@tajinstruments.in" target="_blank" rel="noopener noreferrer">sales@tajinstruments.in</a>
								</label>

						<?php	} 
						 } ?>
					<a href="javascript:void(0);" class="button check_pincode">Check</a></td>
				</form>
		</tr>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

			<tr class="shipping">
				<th><?php _e( 'Shipping', 'woocommerce' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
			</tr>

		<?php endif; ?>

		<?php 
		if(isset($_SESSION['delivery']) && $_SESSION['delivery'] == 'available'){

			foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; 
		}
		?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) :
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
					? sprintf( ' <small>' . __( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
					: '';

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
						<th><?php echo esc_html( $tax->label ) . $estimated_text; ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php _e( 'Total', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>" class="cart_title"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	</table>

	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
