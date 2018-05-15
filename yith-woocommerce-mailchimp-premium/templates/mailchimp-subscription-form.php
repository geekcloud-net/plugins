<?php
/**
 * Subscription form template (used in shortcode and widget)
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Mailchimp
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCMC' ) ) {
	exit;
} // Exit if accessed directly
?>

<!-- BEFORE NEWSLETTER SUBSCRIPTION FORM -->
<?php
do_action( 'before_newsletter_subscription_form' );

if( isset( $before ) ){
	echo $before;
}
?>

<?php if( $enable_style ): ?>
	<style>
		<?php echo $style; ?>
	</style>
<?php endif; ?>

<div class="yith-wcmc-subscription-form woocommerce" id="subscription_form_<?php echo $unique_id ?>">
	<?php if( ! empty( $title ) ): ?>
		<h3><?php echo esc_html( $title ) ?></h3>
	<?php endif;?>

	<?php do_action( 'yith_wcmc_after_subscription_form_title', $list ) ?>

	<?php if( function_exists( 'wc_get_notices' ) ): ?>
	<div class="subscription-notice"><?php
		$success = wc_get_notices( 'yith-wcmc-success' );
		$errors = wc_get_notices( 'yith-wcmc-error' );

		if( ! empty( $success ) ){
			foreach( $success as $notice ){
				wc_print_notice( $notice, 'success' );
			}
		}

		if( ! empty( $errors ) ){
			foreach( $errors as $notice ){
				wc_print_notice( $notice, 'error' );
			}
		}
	?></div>
	<?php endif; ?>

	<?php do_action( 'yith_wcmc_after_subscription_form_notice', $list ) ?>

	<form method="POST" data-hide="<?php echo esc_attr( ! empty( $hide_form_after_registration ) ? $hide_form_after_registration : 'no' )?>">
		<?php foreach( $fields as $id => $field ): ?>

			<?php if( isset( $fields_data[ $id ] ) && $fields_data[ $id ]['public'] ): ?>
			<p>
				<?php if( ! empty( $fields_data[ $id ][ 'name' ] ) && ! $use_placeholders ): ?>
					<label for="<?php echo esc_attr( $id )?>_<?php echo esc_attr( $unique_id ) ?>" ><?php echo function_exists( 'icl_t' ) ? esc_html( icl_t( 'admin_texts_plugin_yith-woocommerce-mailchimp-premium', "yith_wcmc_{$context}_custom_fields[$id]", $field[ 'name' ] ) ) : esc_html( $field[ 'name' ] ) ?></label><br/>
				<?php endif; ?>
				<?php YITH_WCMC_Premium()->print_field( $unique_id, $field, $fields_data[ $id ], $context ) ?>
			</p>
			<?php endif; ?>

		<?php endforeach; ?>

		<?php if( ! empty( $groups_data ) ): ?>
			<?php foreach( $groups_data as $group_id => $group_data ): ?>
				<p>
					<?php if( ! empty( $group_data[ 'name' ] ) && ! $use_placeholders ): ?>
						<label for="group_<?php echo esc_attr( $group_id )?>_<?php echo esc_attr( $unique_id ) ?>" ><?php echo function_exists( 'icl_t' ) ? esc_html( icl_t( 'admin_texts_plugin_yith-woocommerce-mailchimp-premium', "yith_wcmc_{$context}_groups[$group_id]", $group_data[ 'name' ] ) ) : esc_html( $group_data[ 'name' ] ) ?></label><br/>
					<?php endif; ?>
					<?php YITH_WCMC_Premium()->print_groups( $unique_id, $group_data ) ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>

		<input type="hidden" name="email_type" value="<?php echo $email_type ?>" />
		<input type="hidden" name="double_optin" value="<?php echo $double_optin ?>" />
		<input type="hidden" name="update_existing" value="<?php echo $update_existing ?>" />
		<input type="hidden" name="replace_interests" value="<?php echo $replace_interests ?>" />
		<input type="hidden" name="send_welcome" value="<?php echo $send_welcome ?>" />
		<input type="hidden" name="list" value="<?php echo $list ?>" />
		<input type="hidden" name="groups" value="<?php echo $groups ?>" />
		<input type="hidden" name="success_message" value="<?php echo esc_attr( $success_message ) ?>" />

		<?php wp_nonce_field( 'yith_wcmc_subscribe', 'yith_wcmc_subscribe_nonce' )?>
		<input class="submit-form" type="submit" value="<?php echo esc_attr( $submit_label ) ?>" />
	</form>
</div>

<!-- AFTER NEWSLETTER SUBSCRIPTION FORM -->
<?php
if( isset( $after ) ){
	echo $after;
}

do_action( 'after_newsletter_subscription_form' );
?>