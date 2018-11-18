<?php
/**
 * Admin page: Templates children table
 *
 * @link       hi@hello-jeff.com
 * @since      1.0.0
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin/partials
 */

 # Grab the id, page & post type
 $template = (isset($_GET['template'])) ? sanitize_text_field($_GET['template']) : '';
 $id = (isset($_GET['id'])) ? sanitize_text_field($_GET['id']) : '';
 $id = (!empty($id)) ? $id : $template;
 $page = (isset($_GET['page'])) ? sanitize_text_field($_GET['page']) : '';
 $action = (isset($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
 $template_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';

 # Validate page
 if($page != 'wpe-page-templates') return false;

 # Validate id
 if(empty($id)) return false;

 # Setup display name variables
 $template_name = $template_post_types = '';

 # Set the default template
 if($id == 'default'){

   $template_name = 'Default';
   $template_post_types = '(no template)';

 # Grab the template info
 } else {

   # Get the contents of the template file
   $filepath = STYLESHEETPATH . '/' . urldecode($id);
   if(file_exists($filepath)){

     $file_contents = file_get_contents($filepath);

     // Parse the name of the template - https://core.trac.wordpress.org/browser/tags/4.9.8/src/wp-includes/class-wp-theme.php#L1081

     preg_match('|Template Name:(.*)$|mi', $file_contents, $matches);
     $template_name = $matches[1];

     preg_match('|Template Post Type:(.*)$|mi', $file_contents, $matches);
     $template_post_types = '(post types available: ' . $matches[1] . ')';

   }

 }

 ?>
 <div class="wrap">

   <?php
     //////////////////////
     # Display Heading
     //////////////////////
   ?>
   <h2>
     <span><?php echo $template_name; ?></span>
     <small><?php echo $template_post_types; ?></small>
   </h2>

   <div class="notification-box">
       <p>
         <a href="<?php echo get_admin_url() . 'admin.php?page=wpe-page-templates'; ?>">
           <?php _e('&larr; Back to all templates'); ?>
         </a>
       </p>
   </div>

   <?php

     ///////////////////////////////////
     # Handle row action: Detatch
     ///////////////////////////////////
     if(!empty($id) && in_array($action, array('detach'))){

      # Setup sql query
      global $wpdb;
      $sql = "
        UPDATE {$wpdb->postmeta}
        SET meta_value = 'default'
        WHERE 1=1
          AND post_id = %d
          AND meta_key = '_wp_page_template'
          AND meta_value = %s
      ";

       # Update the data
       $prepared = $wpdb->prepare($sql, $id, $template);
       $wpdb->query($prepared);

       ?>
       <div class="updated">
         <p><?php _e('Item has been detatched.'); ?></p>
       </div>
       <?php
       // return false;
     }

     ///////////////////////////////////
     # Handle row action: Detatch all
     ///////////////////////////////////
     if(!empty($id) && in_array($action, array('delete')) && !is_numeric($id)){

      # Setup sql query
      global $wpdb;
      $sql = "
        UPDATE {$wpdb->postmeta}
        SET meta_value = 'default'
        WHERE 1=1
          AND meta_key = '_wp_page_template'
          AND meta_value = %s
      ";

       # Update the data
       $prepared = $wpdb->prepare($sql, $id);
       $wpdb->query($prepared);

       ?>
       <div class="updated">
         <p>Items have been detatched from this template.</p>
       </div>
       <?php
       // return false;
     }

     //////////////////////
     # Display Table
     //////////////////////

     # Create an instance of the template table class
     $table_data = new WPE_PTI_Template_Table();

     # Setup page parameters
     $current_page = $table_data->get_pagenum();
     $current_page = (isset($current_page)) ? $current_page : 1;
     $paged = (isset($_GET['page'])) ? $_GET['page'] : $current_page;
     $paged = (isset($_GET['paged'])) ? $_GET['paged'] : $current_page;
     $paged = (isset($args['paged'])) ? $args['paged'] : $paged;

     # Fetch, prepare, sort, and filter the data
     $table_args = array(
       'template' => $id,
     );
     $table_data->prepare_items($table_args);

   ?>
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
