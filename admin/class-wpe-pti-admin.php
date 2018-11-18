<?php
/**
 * Admin area functionality for this plugin.
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin
 * @author     Jeff Ward <hi@hello-jeff.com>
 */
class WPE_PTI_Admin {

	/////////////////////////
	# Protected constants
	/////////////////////////

	# ID of this plugin.
	private $plugin_name;

	# Version number of this plugin.
	private $version;

	# Assets URL for this plugin.
	private $assets_url;

	# Assets directory for this plugin.
	private $assets_dir;

	//////////////////////////////
	# Constructor function
	//////////////////////////////

	/**
	 * Initialize the class and set its properties.
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $assets_url = null, $assets_dir = null ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->assets_url = (!empty($assets_url)) ? $assets_url : plugin_dir_url( __FILE__ );
		$this->assets_dir = (!empty($assets_dir)) ? $assets_dir : plugin_dir_path( __FILE__ );

	}

	/////////////////////////////////////
	# Action: Register Admin styles
	/////////////////////////////////////

	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in WPE_PTI_Actions as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPE_PTI_Actions will then create the relationship
		 * between the defined hooks and the functions defined in this class.
	 	 *
	 	 * @since    1.0.0
		 */

		wp_enqueue_style( $this->plugin_name, $this->assets_url . 'css/wpe-pti-admin.css', array(), $this->version, 'all' );

	}

	/////////////////////////////////////
	# Action: Register Admin scripts
	/////////////////////////////////////

	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WPE_PTI_Actions as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPE_PTI_Actions will then create the relationship
		 * between the defined hooks and the functions defined in this class.
	 	 *
	 	 * @since    1.0.0
		 */

		wp_enqueue_script( $this->plugin_name, $this->assets_url . 'js/wpe-pti-admin.js', array( 'jquery' ), $this->version, false );

	}

	//////////////////////////////
	# Action: Admin Init
	//////////////////////////////

	public function admin_init() {

		# Redirect: Post Type filter
		$this->admin_post_type_redirect();

		# Redirect: Setup table columns
		$this->admin_setup_table_columns();

	}

	//////////////////////////////
	# Action: Admin Menu
	//////////////////////////////

	public function admin_menu() {

		# Templates table
		add_menu_page('WPE Page Templates Identifier', 'Page Templates', 'activate_plugins', 'wpe-page-templates', array(&$this, 'admin_menu_wpe_pti_table'), 'dashicons-welcome-widgets-menus');

		# Templates settings
		add_submenu_page('wpe-page-templates', 'Settings', 'Settings', 'activate_plugins', 'wpe-pti-settings', array(&$this, 'admin_menu_wpe_pti_settings'));

	}

	///////////////////////////////////
	# Admin menu: templates table
	///////////////////////////////////

	public function admin_menu_wpe_pti_table() {

		# Return callback for templates table
		if(empty($_GET['id']) && empty($_GET['template'])){

			require_once($this->assets_dir . 'class-wpe-pti-table.php');
			require_once($this->assets_dir . 'partials/wpe-pti-templates-table.php');

		# Return callback for templates children table
		} else {

			require_once($this->assets_dir . 'class-wpe-pti-table.php');
			require_once($this->assets_dir . 'class-wpe-pti-table-children.php');
			require_once($this->assets_dir . 'partials/wpe-pti-templates-table-children.php');

		}

	}

	///////////////////////////////////
	# Admin menu: templates settings
	///////////////////////////////////

	public function admin_menu_wpe_pti_settings() {
		require_once($this->assets_dir . 'partials/wpe-pti-templates-settings.php');
	}

	///////////////////////////////////
	# Admin init: Setup table columns
	///////////////////////////////////
	public function admin_setup_table_columns() {

	  # Create a Settings instance
	  $wpe_pti_settings = new WPE_PTI_Admin_Settings();

	  # Get settings
	  $settings = $wpe_pti_settings->get_settings();

	  # Verify whether or not extra column settings is set
	  if(empty($settings['extra_columns'])) return false;

	  # Get all public post types
	  $args = array(
	     'public'   => true,
	     '_builtin' => false
	  );
	  $post_types = get_post_types( $args, $output = 'objects', $operator = 'or' );

	  # Setup ignored post types
	  $ignore_types = array(
	    'attachment'
	  );

	  # Loop through the post types
	  if(!empty($post_types)){
	    foreach ( $post_types  as $post_type_obj ) {
	      $post_type = $post_type_obj->name;
	      if(!in_array($post_type, $ignore_types)){

	        add_filter( 'manage_'.$post_type.'_posts_columns', array(&$this, 'admin_filter_table_columns') );
	        add_action( 'manage_'.$post_type.'_posts_custom_column', array(&$this, 'admin_render_table_columns'), 10, 2);

	      }
	    }
	  }

	}

	///////////////////////////////////
	# Admin init: Filter table columns
	///////////////////////////////////
	public function admin_filter_table_columns($columns) {

	   # Split the columns into fragments
	   $fragmented_columns = array();
	   if(!empty($columns['comments'])){
	     $fragmented_columns['comments'] = $columns['comments'];
	     unset($columns['comments']);
	   }
	   if(!empty($columns['date'])){
	     $fragmented_columns['date'] = $columns['date'];
	     unset($columns['date']);
	   }

	   # Insert the new column
	   $columns['page_template'] = 'Page Template';

	   # Merge & return the fragmented columns
	   $columns = array_merge($columns, $fragmented_columns);
	   return $columns;

	}

	///////////////////////////////////
	# Admin init: Render table columns
	///////////////////////////////////
	public function admin_render_table_columns( $column, $post_id ) {
	  global $post;
	  if($column == 'page_template') {
	    // $thumbnail = get_the_post_thumbnail( $post_id, array(80, 80) );
	    $template = get_page_template_slug( $post_id );
	    $template_link = sprintf('%sadmin.php?page=wpe-page-templates&template=%s&post_type=%s', get_admin_url(), urlencode($template), $post->post_type);
	    echo (!empty($template)) ? '<a href="'.$template_link.'">'.$template.'</a>' : __('default (no template)');
	  }
	}

	///////////////////////////////////
	# Admin init: Post Type filter
	///////////////////////////////////

	public function admin_post_type_redirect() {
	  if(isset($_POST['set_post_type']) && $_POST['set_post_type'] != $_GET['post_type'] && !headers_sent()){

	    # Get the full page URL - https://stackoverflow.com/questions/6768793/
	    $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
	      ? "https"
	      : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	    $full_url = remove_query_arg( array('post_type', 'paged'), $full_url );
	    $new_url = add_query_arg( 'post_type', $_POST['set_post_type'], $full_url );

	    # Redirect to the new URL
	    wp_redirect( $new_url );
	    exit;

	  }
	}

}
