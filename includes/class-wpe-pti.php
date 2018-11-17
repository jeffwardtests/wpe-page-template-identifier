<?php
/**
 * The file that defines the core plugin class
 *
 * @link       hi@hello-jeff.com
 * @since      1.0.0
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/includes
 */
class WPE_PTI {

	/////////////////////////
	# Protected constants
	/////////////////////////

	# Set the actions that's responsible for maintaining and registering all hooks for the plugin.
	protected $actions;

	# Set the unique identifier of this plugin.
	protected $plugin_name;

	# Set the current version of the plugin.
	protected $version;

	//////////////////////////////
	# Constructor function
	//////////////////////////////

	public function __construct() {

		# Set the version number
		$this->version = (defined( 'PLUGIN_NAME_VERSION')) ? PLUGIN_NAME_VERSION : '1.0.0';

		# Set the plugin slug name
		$this->plugin_name = 'wpe-pti';

		# Load dependencies
		$this->load_dependencies();

		# Define admin hooks
		$this->define_admin_hooks();

	}

	//////////////////////////////
	# Load plugin dependencies
	//////////////////////////////

	/**
	 * Include the following files that make up the plugin:
	 *
	 * - WPE_PTI_Actions. Orchestrates the hooks of the plugin.
	 * - WPE_PTI_Admin. Defines all hooks for the admin area.
	 * - WPE_PTI_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the plugin's actions which will be used to register hooks & filters
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		# Include core actions & filters class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpe-pti-actions.php';

		# Include admin actions & filters class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpe-pti-admin.php';

		# Create an Actions instance
		$this->actions = new WPE_PTI_Actions();

	}

	//////////////////////////////
	# Define admin hooks
	//////////////////////////////

	/**
	 * Register all of the hooks related to the admin area functionality
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		# Create an Admin instance
		$plugin_admin = new WPE_PTI_Admin( $this->get_plugin_name(), $this->get_version() );

		# Enqueue styles & scripts
		$this->actions->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->actions->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	///////////////////////////////////////////
	# Run the actions to execute all hooks
	///////////////////////////////////////////

	public function run() {
		$this->actions->run();
	}

	/////////////////////////////////
	# Get the name of the plugin
	/////////////////////////////////

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	//////////////////////////////////////////////////////
 	# Get the reference to the plugin's actions class
 	//////////////////////////////////////////////////////

	/**
	 * @since     1.0.0
	 * @return    WPE_PTI_Actions    Orchestrates the hooks of the plugin.
	 */
	public function get_actions() {
		return $this->actions;
	}

	//////////////////////////////////////////
	# Get the version number of this plugin
	//////////////////////////////////////////

	public function get_version() {
		return $this->version;
	}

}
