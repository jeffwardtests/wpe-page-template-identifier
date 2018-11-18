<?php
/**
 * Admin page: Templates settings
 *
 * @link       hi@hello-jeff.com
 * @since      1.0.0
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin/partials
 */

 # Create a Settings instance
 $wpe_pti_settings = new WPE_PTI_Admin_Settings();

 # Submit settings
 $wpe_pti_settings->submit_settings();

 # Get settings
 $settings = $wpe_pti_settings->get_settings();

?>
<div class="wrap">

   <?php
     ////////////////////////
     # Display Heading
     ////////////////////////
   ?>
   <h2><?php _e('WPEngine Page Template Identifier Settings'); ?></h2>

   <div class="notification-box">
       <p><?php _e('Update parameters used to for the WPEngine Page Template Identifier here.'); ?></p>
   </div>

   <form class="settings-form" method="post">

     <?php
       /////////////////////////////////////
       # Field: Enable extra columns
       /////////////////////////////////////
       $key = 'extra_columns';
       $label = __('Add additonal column to reveal select templates in post type tables?');
       $val = $settings[$key];
       $checked_on = (!empty($val)) ? 'checked="checekd"' : '';
       $checked_off = (empty($val)) ? 'checked="checekd"' : '';
     ?>
     <div class="settings-row">
       <label for="wpe_pti_settings[<?php echo $key; ?>]"><?php echo $label; ?></label>
       <label class="radio-select">
         <input type="radio" name="wpe_pti_settings[<?php echo $key; ?>]" id="wpe_pti_settings[<?php echo $key; ?>]-on" value="1" <?php echo $checked_on; ?> />
         <span><?php _e('Yes'); ?></span>
       </label>
       <label class="radio-select">
         <input type="radio" name="wpe_pti_settings[<?php echo $key; ?>]" id="wpe_pti_settings[<?php echo $key; ?>]-off" value="0" <?php echo $checked_off; ?> />
         <span><?php _e('No'); ?></span>
       </label>
     </div><!-- .settings-row -->

       <?php
         /////////////////////////////////////
         # Loop through additional fields
         /////////////////////////////////////
         /*
         $fields = array(
           'extra_field' => __('Extra field:'),
         );

         # Loop through fields
         foreach($fields as $key => $label){
           $val = $settings[$key];
         ?>
         <div class="settings-row">
           <label for="wpe_pti_settings[<?php echo $key; ?>]"><?php echo $label; ?></label>
           <input type="text" name="wpe_pti_settings[<?php echo $key; ?>]" id="wpe_pti_settings[<?php echo $key; ?>]" value="<?php echo $val; ?>" />
         </div><!-- .settings-row -->
         <?php
         }
         */
       ?>

       <?php
         ////////////////////////
         # Form Submit
         ////////////////////////
       ?>
       <?php # Include nonce ?>
       <?php wp_nonce_field( 'wpe_pti_submit_settings', 'wpe_pti_submit_settings' ); ?>

       <p class="submit">
         <?php # Save button ?>
         <?php // submit_button(); ?>
         <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
         &nbsp;
         <?php # Revert button ?>
         <button type="submit" name="revert" value="1" class="button button-secondary button-large"><?php _e('Revert Settings'); ?></button>
       </p>

    </form>
 </div>
 <?php
