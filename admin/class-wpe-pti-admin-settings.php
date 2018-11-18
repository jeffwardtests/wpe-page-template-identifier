<?php
/**
 * Admin settings for this plugin.
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin
 * @author     Jeff Ward <hi@hello-jeff.com>
 */
class WPE_PTI_Admin_Settings {

	//////////////////////////////
	# Constructor function
	//////////////////////////////

	/**
	 * @since    1.0.0
	 */
	public function __construct() {

	}

	/**
	 * Get Admin Settings defaults
	 * @since    1.0.0
	 * @param    string    $key
	 * @access   public
	 */
	public function default_settings($key = null){
	  $settings = array(
	    'extra_columns' => 1,
	    // 'extra_field' => '',
	  );
	  return (!empty($key)) ? $settings[$key] : $settings;
	}

	/**
	 * Get Admin Settings
	 * @since    1.0.0
	 * @param    string    $key
	 * @access   public
	 */
	public function get_settings($key = null){
	  $defaults = $this->default_settings();
	  $settings = get_option('wpe_pti_settings');
	  $settings = (empty($settings)) ? $defaults : $settings;
	  return (isset($key)) ? $settings[$key] : $settings;
	}

	/**
	 * Revert Admin Settings
	 * @since    1.0.0
	 * @access   public
	 */
	public function revert_settings(){

		# Get the defaults & update option
		$defaults = $this->default_settings();
		update_option('wpe_pti_settings', $defaults);

	}

	/**
	 * Save Admin Settings
	 * @since    1.0.0
	 * @param    array    $submit_args
	 * @param    bool   $replace
	 * @access   public
	 */
	public function save_settings($submit_args = array(), $replace = false){

		# Merge settings
		if($replace === false){

			$settings = $this->get_settings();
			$submit_args = array_merge((array) $settings, (array) $submit_args);

		}

		# Update option
		update_option('wpe_pti_settings', $submit_args);

	}

	/**
	 * Submit Admin Settings callback
	 * @since    1.0.0
	 * @access   public
	 */
	public function submit_settings(){

	  # Validate submission
	  $nonce = (isset($_POST['wpe_pti_submit_settings'])) ? $_POST['wpe_pti_submit_settings'] : '';
	  if( !wp_verify_nonce( $nonce, 'wpe_pti_submit_settings' ) ) return false;

	  ////////////////////
	  # Revert settings
	  ////////////////////
	  if(isset($_REQUEST['revert'])){

	    # Revert the settings
	    $this->revert_settings();

	    # Display message
	    echo '<div class="updated">
				<p>'.__('Settings have been reverted successfully.').'</p>
			</div>';
	    return;

	  }

	  ////////////////////
	  # Save settings
	  ////////////////////
	  $submit_args = (isset($_POST['wpe_pti_settings'])) ? $_POST['wpe_pti_settings'] : array();
	  if(isset($submit_args)){

			# Save the settings
			$this->save_settings($submit_args);

	    # Display message
	    echo '<div class="updated">
				<p>'.__('Settings have been saved successfully.').'</p>
			</div>';
	    return;

	  ////////////////////
	  # Save error
	  ////////////////////
	  } else {

	    # Display message
	    echo '<div class="updated error">
	      <p>'.__('There was an error saving your settings.').'</p>
	    </div>';
	    return;

	  }

	}

}
