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
	public function __construct( $plugin_name, $version, $assets_dir = null ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->assets_dir = (!empty($assets_dir)) ? $assets_dir : plugin_dir_url( __FILE__ );

	}


	//////////////////////////////
	# Register Admin styles
	//////////////////////////////

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

		wp_enqueue_style( $this->plugin_name, $this->assets_dir . 'css/wpe-pti-admin.css', array(), $this->version, 'all' );

	}

	//////////////////////////////
	# Register Admin scripts
	//////////////////////////////

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

		wp_enqueue_script( $this->plugin_name, $this->assets_dir . 'js/wpe-pti-admin.js', array( 'jquery' ), $this->version, false );

	}

}
