<?php
/**
 * Setup menus in WP admin.
 *
 * @version		1.0
 * @category	Class
 * @author      Actuality Extensions
 * @package     WooCommerce_Customer_Relationship_Manager/Classes
 * @since       2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Welcome' ) ) :

/**
 * WC_POS_Welcome Class
 */
class WC_POS_Welcome {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array($this, 'about_page') );
	    add_action( 'admin_head', array( $this, 'admin_head' ) );

	}

	public function about_page()
	{
		$welcome_page_name  = __( 'About Point of Sale', 'wc_point_of_sale' );
		$welcome_page_title = __( 'About Point of Sale', 'wc_point_of_sale' );
		if( isset($_GET['page']) && $_GET['page'] == WC_POS_TOKEN.'-about'){
			//jquery-ui-progressbar
			$page = add_submenu_page( WC_POS_TOKEN, $welcome_page_title, $welcome_page_name, 'manage_woocommerce', WC_POS_TOKEN.'-about', array( $this, 'about_screen' ) );
			add_action( 'admin_print_styles-' . $page, array( $this, 'admin_css' ) );			
		}
	}

	public function admin_head()
	{
		remove_submenu_page( WC_POS_TOKEN, WC_POS_TOKEN.'-about' );
	}

	/**
	 * admin_css function.
	 */
	public function admin_css() {
		wp_enqueue_style( 'wc-pos-activation', WC_POS()->assets_url . 'css/activation.css', array(), WC_VERSION );
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function intro() {
		// Drop minor version if 0
		$major_version = substr( WC_POS()->_version, 0, 5 );
		?>
		<h1><?php printf( __( 'Welcome to WooCommerce POS %s', 'wc_point_of_sale' ), $major_version ); ?></h1>

		<div class="about-text woocommerce-about-text">
			<?php
				if ( ! empty( $_GET['wc-installed'] ) ) {
					$message = __( 'Thanks, all done!', 'wc_point_of_sale' );
				} elseif ( ! empty( $_GET['wc-updated'] ) ) {
					$message = __( 'Thank you for updating to the latest version!', 'wc_point_of_sale' );
				} else {
					$message = __( 'Thanks for installing!', 'wc_point_of_sale' );
				}

				printf( __( '%s WooCommerce Point of Sale %s allows you to place orders through a POS interface swiftly using the WooCommerce products and orders database.', 'wc_point_of_sale' ), $message, $major_version );
			?>
		</div>

		<div class="wc-badge"><?php printf( __( 'Version %s', 'wc_point_of_sale' ), WC_POS()->_version ); ?></div>
			<a href="<?php echo esc_url( admin_url('admin.php?page=' . WC_POS_TOKEN . '_settings') ); ?>" class="button button-primary"><?php _e( 'Settings', 'wc_point_of_sale' ); ?></a>
			<a href="http://actualityextensions.com/documentation/" class="docs button button-primary"><?php _e( 'Docs', 'wc_point_of_sale' ); ?></a>
		<?php
	}

  	/**
	* Output the about screen.
	*/
	public function about_screen() {
	?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>
						
			<?php include_once 'views/html-about-news.php'; ?>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url('admin.php?page=' . WC_POS_TOKEN . '_registers')); ?>"><?php _e( 'Go to Point of Sale → Registers', 'wc_point_of_sale' ); ?></a>
			</div>
		
		</div>
	<?php
  	}


}

endif;

return new WC_POS_Welcome();
