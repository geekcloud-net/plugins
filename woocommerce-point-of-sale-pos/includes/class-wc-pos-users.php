<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Pos_Users' ) ) :

/**
 * WC_Pos_Users Class
 */
class WC_Pos_Users {

	/**
	 * @var WC_Pos_Users The single instance of the class
	 * @since 1.9
	 */
  protected static $_instance = null;

	/**
	 * Main WC_Pos_Users Instance
	 *
	 * Ensures only one instance of WC_Pos_Users is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Users Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
    if( !defined('INIT_POS_USERS')){
      global $pos_updated_users;
      $pos_updated_users = array();

      add_action( 'profile_update', array($this, 'profile_update'), 10, 1 );
      add_action( 'user_register', array($this, 'profile_update'), 10, 1 );
      add_action( 'updated_user_meta', array($this, 'updated_user_meta'), 40, 4 );      
      define('INIT_POS_USERS', true);
    }
  }

  public function updated_user_meta( $meta_id, $object_id, $meta_key, $_meta_value )
  {
    $this->profile_update( $object_id );
  }

  public function profile_update( $user_id )
  {
    global $wpdb, $pos_updated_users;

    if( !isset($pos_updated_users[$user_id]) ){
      $modified_date = current_time( 'mysql', 1 );
      $result = $wpdb->query("UPDATE {$wpdb->users} SET user_modified_gmt = '{$modified_date}' WHERE ID = {$user_id} ");
      $pos_updated_users[$user_id] = true;
    }
  }

	public function display()
	{
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>
			<div id="lost-connection-notice" class="error hidden">
				<p>
					<span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
					<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
				</p>
			</div>
			<form action="" method="post" id="edit_wc_pos_users">
			<p class="description"><?php _e( 'The table below shows cashier who have an outlet assigned to them and their activity. To add a user to an outlet, go to the users profile and assign an outlet from there.', 'wc_point_of_sale' ); ?></p>
				<?php
					$users_table = WC_POS()->users_table();
					$users_table->search_box( 'Search', 'wc_pos_users_is_search' );
					$users_table->prepare_items();
					$users_table->display();
				?>
			</form>
		</div>

		<?php
	}
	

	public function get_data($username = ''){
        global $wpdb;
        $data = array();

        $user_query = new WP_User_Query(array( 'meta_key' => 'outlet', 'meta_value' => '', 'meta_compare' => '!=') );

        if ( ! empty( $user_query->results ) ) {
          foreach ( $user_query->results as $user ) {
            $_no_exist = 0;
            if( isset($_REQUEST['s']) && !empty($_REQUEST['s']) && $_GET['page'] == WC_POS()->id_users ){
              $__s = $_REQUEST['s'];
              if ( !stristr($user->data->display_name, $__s) ) $_no_exist++;
              if ( !stristr($user->data->user_nicename, $__s) ) $_no_exist++;
              if($_no_exist == 2) continue;
            }
            if (isset($_POST['_usernames_filter']) && !empty($_POST['_usernames_filter'])) {
              $userid = $_POST['_usernames_filter'];
              if($user->data->ID != $userid) continue;
            }
            if (isset($_POST['_outlets_filter']) && !empty($_POST['_outlets_filter'])) {
              $outlet_id = $_POST['_outlets_filter'];
              if(esc_attr( get_user_meta( $user->data->ID, 'outlet', true ) ) != $outlet_id) continue;
            }
            if(!empty($username) && strpos( strtoupper( $user->data->user_nicename ), strtoupper( $username) ) === false){
              continue;
            }

           $count_orders = esc_attr( get_user_meta( $user->data->ID, 'wc_pos_count_orders', true ) );
           $sales        = $this->get_user_sales($user->data->ID);
            $data[$user->data->ID] = array(
              'ID'         => $user->data->ID,
              'name'       => $user->data->display_name,
              'username'   => $user->data->user_nicename,
              'outlet'     => esc_attr( get_user_meta( $user->data->ID, 'outlet', true ) ),
              'orders'     => $count_orders ? $count_orders : 0,
              'sales'      => $sales,
              'last_login' => $this->get_last_login($user->data->ID),
              'logged_in'  => $this->is_logged_in($user->data->ID),
            );
          }
        }
        return $data;
  }

  public function get_user_sales($user_id)
  {
  	global $wpdb;
  	$query = "SELECT SUM(meta.meta_value) FROM {$wpdb->posts} posts
  				LEFT JOIN {$wpdb->postmeta} meta ON(meta.post_id = posts.ID AND meta.meta_key = '_order_total')
  				LEFT JOIN {$wpdb->postmeta} type ON(type.post_id = posts.ID AND type.meta_key = 'wc_pos_order_type')
				WHERE posts.post_type = 'shop_order' AND posts.post_author = {$user_id} AND meta.meta_key = '_order_total' AND type.meta_value = 'POS'
  		";
  	$result = $wpdb->get_var($query);
  	return $result;
  }
  public function get_data_names(){
    $data = self::get_data();
    $names_list = array();
    foreach ($data as $value) {
      $names_list[$value['ID']] = $value['name'];
    }
    return $names_list;
  }

  function get_last_login($user_id) {
   $last_login = get_user_meta($user_id, 'last_login', true);

   if(!empty($last_login)){
      $date_format = get_option('date_format') . ' ' . get_option('time_format');
      $the_last_login = mysql2date($date_format, $last_login, false);
    }else{
      $the_last_login = 'None';
    }
   return $the_last_login;
  }

  function is_logged_in($user_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

    $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE _edit_last = $user_id");
    if ( !$db_data )
      return '<span class="user-register-logged-out tips" data-tip="No"></span>';

    $row = $db_data[0];
    if ( strtotime($row->opened) > strtotime($row->closed) )
      return '<span class="user-register-logged-in tips" data-tip="User logged in at '.$row->name.'"></span>';
    else
      return '<span class="user-register-logged-out tips" data-tip="No"></span>';

  }
}
return new WC_Pos_Users;
endif;
