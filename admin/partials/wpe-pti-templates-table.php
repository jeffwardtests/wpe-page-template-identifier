<?php
/**
 * Admin page: Templates table
 *
 * @link       hi@hello-jeff.com
 * @since      1.0.0
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin/partials
 */

 # Create an instance of the table class
 $table_data = new WPE_PTI_Table();

 # Setup page parameters
 $current_page = $table_data->get_pagenum();
 $current_page = (isset($current_page)) ? $current_page : 1;
 $paged = (isset($_GET['page'])) ? $_GET['page'] : $current_page;
 $paged = (isset($_GET['paged'])) ? $_GET['paged'] : $current_page;
 $paged = (isset($args['paged'])) ? $args['paged'] : $paged;

 # Fetch, prepare, sort, and filter the data
 $table_data->prepare_items();

 ?>
 <div class="wrap">

   <?php
     //////////////////////
     # Display Heading
     //////////////////////
   ?>
   <h2><?php _e('WPEngine Page Template Identifier'); ?></h2>

   <div class="notification-box">
       <p><?php _e('This page lists all of the page templates used for the website.'); ?></p>
       <?php /* ?><p>Parent directory: <code><?php echo TEMPLATEPATH . '/'; ?></code></p><?php */ ?>
       <p><?php _e('Parent directory:'); ?> <code><?php echo STYLESHEETPATH . '/'; ?></code></p>
   </div>

   <?php # Table ?>
   <form id="items-table" class="wpe-pti" method="post">

     <?php # Include nonce ?>
     <?php // wp_nonce_field( 'wpe_pti_table', 'wpe_pti_table' ); ?>

     <?php # Current page ?>
     <input type="hidden" name="paged" value="<?php echo $paged; ?>" />

     <?php

       //////////////////////
       # Render the table
       //////////////////////
       $table_data->display();

           /*
             $table_data->display() calls the following:
             WP_List_Table::display_rows_or_placeholder()
             WP_List_Table::display_rows()
             WP_List_Table::single_row()
             WP_List_Table::single_row_columns()
           */

         ?>

     </form>

   </div><!-- .wrap -->
 <?php
