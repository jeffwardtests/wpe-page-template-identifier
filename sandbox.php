<?php


/////////////////////
# Add admin menu
/////////////////////
add_action('admin_menu', 'add_wpe_pti_admin');
function add_wpe_pti_admin(){
  add_menu_page('WPE Page Templates Identifier', 'Page Templates', 'activate_plugins', 'wpe-page-templates', 'render_wpe_pti_admin', 'dashicons-welcome-widgets-menus');
}


//////////////////////////////////
# Render admin page (sandbox)
//////////////////////////////////
function render_wpe_pti_admin_sandbox(){

  //////////////////////////
  # Parse url
  //////////////////////////
  echo '<h3>Parse url</h3>';

  # Get the full page URL - https://stackoverflow.com/questions/6768793/
  $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ? "https"
    : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

  $full_url = remove_query_arg( 'template_slug', $full_url );
  $full_url = add_query_arg( 'template_slug', urlencode('test/init.php'), $full_url );

  echo $full_url;

  //////////////////////////
  # Get url encoded value
  //////////////////////////
  echo '<h3>Get url encoded value</h3>';
  $template_slug = (isset($_GET['template_slug'])) ? sanitize_text_field($_GET['template_slug']) : 'default';

  echo $template_slug;


  //////////////////////////
  # Get all post types
  //////////////////////////
  echo '<hr />';
  echo '<h3>All post types</h3>';
  $args = array(
     'public'   => true,
     '_builtin' => false
  );
  $selected_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';
  $post_types = get_post_types( $args, $output = 'names', $operator = 'or' );
  if(!empty($post_types)){
    echo '<select name="post_type_select">';
    foreach ( $post_types  as $post_type ) {
      $selected = ($selected_post_type == $post_type) ? 'selected="selected"' : '';
       echo '<option '.$selected.' value="'.$post_type.'">' . $post_type . '</p>';
    }
    echo '<select>';
  }

  //////////////////////////
  # Get all page templates
  //////////////////////////
  echo '<hr />';
  echo '<h3>All templates</h3>';

  # Get the full page URL - https://stackoverflow.com/questions/6768793/
  $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ? "https"
    : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

  # Remove the template slug parameter from the URL
  $full_url = remove_query_arg( 'template_slug', $full_url );

  # Get the templates
  $templates = get_page_templates( $current_post = null, $template_post_type = 'page' );

  # Append default to the templates
  $templates = array_merge(array('(no template)' => 'default'), $templates);

  # Loop through each template and provide a link
  foreach( $templates as $template_name => $template_filename ) {

    # Add the template slug parameter to the new URL
    $template_link = add_query_arg( 'template_slug', urlencode($template_filename), $full_url );

    echo "{$template_name} (<a href='{$template_link}'>{$template_filename}</a>)<br />";

  }

  echo '<pre>';
  print_r($templates);
  echo '</pre>';

  echo '<hr />';

  /////////////////////////////
  # Get all published pages
  /////////////////////////////
  echo '<h3>All pages</h3>';

  $post_type = 'page';
  $post_status = 'publish';

  global $wpdb;
  $sql = " SELECT * FROM {$wpdb->posts} WHERE 1=1 AND `post_type` = %s AND `post_status` = %s ";
  $results = $wpdb->get_results($wpdb->prepare($sql, $post_type, $post_status));

  if(!empty($results)){
    foreach($results as $post){

        $page_template = get_page_template_slug( $post->ID );
        $page_template = (empty($page_template)) ? '(no template)' : $page_template;

        /*
        echo '<pre>';
        print_r($post);
        echo '</pre>';
        echo '<br> Title: ' . $post->post_title;
        echo '<br> Template: ' . $page_template;
        */

    }
  }
  echo '<hr />';

  /////////////////////////////
  # Get posts by template
  /////////////////////////////
  echo '<h3>Get posts by template (default)</h3>';

  $post_type = 'page';
  $post_status = 'publish';
  $template_slug = (isset($_REQUEST['template_slug'])) ? sanitize_text_field($_REQUEST['template_slug']) : 'default';

  global $wpdb;
  $sql = "
    SELECT * FROM {$wpdb->posts} AS _p
    INNER JOIN {$wpdb->postmeta} AS _pm
      ON (_p.ID = _pm.post_id)
    WHERE 1=1
    AND _pm.meta_key = '_wp_page_template'
    AND _pm.meta_value = %s
    AND _p.post_type = %s
    AND _p.post_status = %s
  ";
  $prepared = $wpdb->prepare(
      $sql,
      $template_slug,
      $post_type,
      $post_status
  );
  $results = $wpdb->get_results($prepared);
  if(!empty($results)){
    foreach($results as $post){

        echo '<pre>';
        print_r($post);
        echo '</pre>';
        echo '<hr />';

    }
  }
}


//////////////////////////////////
# Render Admin Table page
//////////////////////////////////
function render_wpe_pti_admin(){

    # Return single entry
    if(!empty($_GET['id']) || !empty($_GET['template'])) return render_wpe_pti_single();

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

        <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
            <p>This page lists all of the page templates used for this website.</p>
            <?php /* ?><p>Theme location: <code><?php echo TEMPLATEPATH . '/'; ?></code></p><?php */ ?>
            <p>Theme location: <code><?php echo STYLESHEETPATH . '/'; ?></code></p>
        </div>

        <?php # Table ?>
        <form id="items-table" method="post">

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
  }














  ////////////////////////////////////////////////

  # Admin Table 1

  ////////////////////////////////////////////////

  // Reference: https://wordpress.org/plugins/custom-list-table-example/

  if(!class_exists('WP_List_Table')){
     require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }

  class WPE_PTI_Table extends WP_List_Table {

   ///////////////////////////
   # Constructor function
   ///////////////////////////

   function __construct(){
       global $status, $page;

       # Set parent defaults
       $args = array(
           'singular'  => 'template',     //singular name of the listed records
           'plural'    => 'templates',    //plural name of the listed records
           'ajax'      => false          //does this table support ajax?
       );
       parent::__construct($args);

   }

   ///////////////////////
   # Register columns
   ///////////////////////
   function get_columns(){
       $columns = array(
           'cb' => '<input type="checkbox" />', // Render a checkbox instead of text
           'title' => 'Template name',
           'slug' => 'Template location',
           'count' => 'Attached pages',
           // 'date' => 'Date',
       );
       return $columns;
   }

   ///////////////////////
   # Sortable columns
   ///////////////////////
   // true means it's already been sorted
   function get_sortable_columns() {
       $sortable_columns = array(
           'title'     => array('title', false),
           // 'date'  => array('date',false),
       );
       return $sortable_columns;
   }

    ///////////////////////
    # Column: Checkbox
    ///////////////////////
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'ids',  // $this->_args['singular']
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }

     ///////////////////////
     # Column: Title
     ///////////////////////
     function column_title($item){

        # Get the current page
        $page_slug = (isset($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : '';

        # Row action labels
        $view_label = 'View attached pages';
        $edit_label = 'Edit';
        $delete_label = 'Detach all pages';

         # Build row actions
         $actions = array(
             'view' => sprintf('<a href="?page=%s&template=%s">%s</a>', $page_slug, $item['id'], $view_label),
             // 'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">%s</a>', $page_slug, 'edit', $item['id'], $edit_label),
         );
         if(!empty($item['id']) && $item['id'] != 'default'){
           $actions['delete'] = sprintf('<a href="?page=%s&action=%s&id=%s">%s</a>', $page_slug, 'delete', $item['id'], $delete_label);
         }

         # Return the title column contents
         return sprintf('<a href="?page=%1$s&template=%2$s">%3$s</a> %4$s',
             /*$1%s*/ $page_slug,
             /*$2%s*/ $item['id'],
             /*$3%s*/ $item['title'],
             /*$4%s*/ $this->row_actions($actions)
         );

     }

     /////////////////////////////
     # Regsiter Bulk actions
     /////////////////////////////
     function get_bulk_actions() {
         $actions = array(
             'delete'    => 'Detach all attached pages',
         );
         return $actions;
     }

     /////////////////////////////
     # Process Bulk actions
     /////////////////////////////
     function process_bulk_action() {

      # Grab the selected ids
      global $wpdb;
      $ids = (isset($_REQUEST['ids'])) ? sanitize_text_field($_REQUEST['ids']) : '';

       # Loop through actions
       switch( $this->current_action() ){

         # Delete items
         case 'delete':

           # Verify template submission nonce
           $nonce = (isset($_POST['wpe_pti_table'])) ? sanitize_text_field($_POST['wpe_pti_table']) : '';
           // if( !wp_verify_nonce( $nonce, 'wpe_pti_table' ) ) return false;

           # Delete
           if(!empty($ids)){
             $ids = (is_array($ids)) ? implode(',', $ids) : $ids;


             // Delete logic goes here
             // DELETE FROM ...

             echo '
              <div class="updated">
          			<p>The following templates have been detached: '.$ids.'</a></p>
          		</div>
              ';

           }

         break;

       }

     }

     ///////////////////////
      # Render row cells
     ///////////////////////
     function column_default($item, $column_name){

         switch($column_name){

           /////////////
           # Path (slug)
           /////////////
           case 'slug':
               return $item['slug'];
               break;

           /////////////
           # Count
           /////////////
           case 'count':

              # Get the current page
              $page_slug = (isset($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : '';

              $count = $this->count_total_attached($item['slug']);
              return sprintf('<a href="?page=%s&template=%s">%s</a>', $page_slug, $item['id'], $count);

              break;

           /////////////
           # Date
           /////////////
           case 'date':
             $date = $item[$column_name];
             // $return = mysql2date('d M Y', $date);
             $return = mysql2date('D F jS, Y', $date);
             $return .= ' <br /><small>' . mysql2date('g:ia', $date) . '</small>';
             return $return;
             break;

           /////////////
           # Default
           /////////////
           default:
              // Display the item array to debug unidentified objects
              return '<pre>' . print_r($item). '</pre>';
             break;

         }
     }

     ///////////////////////
      # Get results
     ///////////////////////
     function prepare_items($args = array()) {

         # Handle bulk actions
         $this->process_bulk_action();

         # Setup page parameters
         $current_page = $this->get_pagenum();
         $current_page = (isset($current_page)) ? $current_page : 1;
         $paged = (isset($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : $current_page;
         $paged = (isset($_REQUEST['paged'])) ? sanitize_text_field($_REQUEST['paged']) : $current_page;
         $paged = (isset($args['paged'])) ? $args['paged'] : $paged;

         # Setup pagination parameters
         $per_page = (isset($args['per_page'])) ? $args['per_page'] : 10;
         $offset = ($paged - 1) * $per_page;

         # Setup order parameters
         $orderby = (isset($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'title';
         $order = (isset($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'asc';

         # Setup table parameters
         $columns = $this->get_columns();
         $sortable = $this->get_sortable_columns();
         $hidden = array();

         # Generate headers
         $this->_column_headers = array($columns, $hidden, $sortable);

         # Get the templates
         $template_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';
         $templates = get_page_templates( $current_post = null, $template_post_type );

          // You could prepend the default here to sort it as well

         # Sort items alphabetically
         if($orderby == 'title'){
           if($order == 'asc'){
             asort($templates);
           } else {
             arsort($templates);
           }
         }

         # Prepend the default template
         $templates = array_merge(array('Default (no template)' => 'default'), $templates);

         # Count the total number of templates found
         $total = count($templates);

         # Slice the array for pagination - https://stackoverflow.com/questions/31985295/
         $results = array_slice($templates, $offset, $per_page);

         # Format result items and append to new array
         $new_results = array();
         if(!empty($results)){
           foreach($results as $key => $val){
             $new_results[] = array(
               'id' => urlencode($val),
               'title' => $key,
               'slug' => $val,
             );
           }
         }

         # Set the data
         $this->items = $new_results;

         # Set pagniation args
         $pagination_args = array(
             'total_items' => $total,
             'per_page'    => $per_page,
             'total_pages' => ceil($total/$per_page),
         );
         $this->set_pagination_args( $pagination_args );

     }

     //////////////////////////////////////
    # Append post types dropdown select
    //////////////////////////////////////
    // https://stackoverflow.com/questions/23859559/
    protected function extra_tablenav( $which ) {

        # Set the default bulk actions nonce field
       if('top' === $which):
           // wp_nonce_field( 'bulk-' . $this->_args['plural'] );
       ?>
       <div class="alignleft actions bulkactions">
    			<label for="post-type-selector" class="screen-reader-text___ ">Post type:</label>
            <?php
              # Get the selected post type
              $selected_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';

              # Get all public post types
              $args = array(
                 'public'   => true,
                 '_builtin' => false
              );
              $post_types = get_post_types( $args, $output = 'objects', $operator = 'or' );

              # List the post types
              if(!empty($post_types)){
                echo '<select id="post-type-selector" name="post_type" style="float: none;">';
                foreach ( $post_types  as $post_type_obj ) {
                  $post_type = $post_type_obj->name;
                  $post_type_label = $post_type_obj->label;
                  $selected = ($selected_post_type == $post_type) ? 'selected="selected"' : '';
                   echo '<option '.$selected.' value="'.$post_type.'">' . $post_type_label . '</p>';
                }
                echo '<select>';
              }

            ?>
            <input type="submit" id="select-post-type" class="button action" value="Filter" />
    		</div>
       <?php
      endif; // endif (top)

     }

    ///////////////////////////////////////
    # Count number of attached pages
    ///////////////////////////////////////
    function count_total_attached($template_slug = 'default', $post_type = null) {

        global $wpdb;
        $sql = "
          SELECT COUNT(*) FROM {$wpdb->posts} AS _p
          INNER JOIN {$wpdb->postmeta} AS _pm
            ON (_p.ID = _pm.post_id)
          WHERE 1=1
          AND _pm.meta_key = '_wp_page_template'
          AND _pm.meta_value = %s
        ";

        # Prepare the query
        if(!empty($post_type)){

          // Post type has been specified
          $sql .= " AND _p.post_type = %s ";
          $prepared = $wpdb->prepare(
              $sql,
              $template_slug,
              $post_type
          );

        } else {

          // Without post type
          $prepared = $wpdb->prepare(
              $sql,
              $template_slug
          );

        }

        # Get the total
        $total = $wpdb->get_var($prepared);

        return $total;

    }

  }
















  /////////////////////////////////////
  # Render Admin Table single item
  /////////////////////////////////////
  function render_wpe_pti_single(){

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
      $template_post_types = '(template not selected)';

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
        $template_post_types = '(connected post types: ' . $matches[1] . ')';

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
            <p>Item has been detatched.</p>
          </div>
          <?php
          // return false;
        }

          ///////////////////////////////////
          # Handle row action: Detatch
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
      <form id="items-table" method="post">

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
  }



    ////////////////////////////////////////////////

    # Admin Table - Template relationships

    ////////////////////////////////////////////////

    // Reference: https://wordpress.org/plugins/custom-list-table-example/
    class WPE_PTI_Template_Table extends WPE_PTI_Table {

     ///////////////////////////
     # Constructor function
     ///////////////////////////
     function __construct(){
         global $status, $page;

         # Set parent defaults
         $args = array(
             'singular'  => 'template',     //singular name of the listed records
             'plural'    => 'templates',    //plural name of the listed records
             'ajax'      => false          //does this table support ajax?
         );
         parent::__construct($args);
     }

     ///////////////////////
     # Register columns
     ///////////////////////
     function get_columns(){
         $columns = array(
             'cb' => '<input type="checkbox" />', // Render a checkbox instead of text
             'id' => 'ID',
             'thumbnail' => '',
             'title' => 'Post Title',
             'type' => 'Post Type',
             'status' => 'Post Status',
             'date' => 'Post Date',
         );
         return $columns;
     }

     ///////////////////////
     # Sortable columns
     ///////////////////////
     // true means it's already been sorted
     function get_sortable_columns() {
         $sortable_columns = array(
             'title'     => array('post_title', false),
             'id'     => array('id', true),
             'date'  => array('post_date',false),
         );
         return $sortable_columns;
     }

      ///////////////////////
      # Column: Checkbox
      ///////////////////////
      function column_cb($item){
          $post_id = $item['ID'];
          return sprintf(
              '<input type="checkbox" name="%1$s[]" value="%2$s" />',
              /*$1%s*/ 'ids',  // $this->_args['singular']
              /*$2%s*/ $post_id                //The value of the checkbox should be the record's id
          );
      }

       ///////////////////////
       # Column: Title
       ///////////////////////
       function column_title($item){

         # Get the current page
         $page_slug = (isset($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : '';

         # Get the current template
         $template_slug = (isset($_REQUEST['template'])) ? sanitize_text_field($_REQUEST['template']) : '';

          # Row action labels
          $edit_label = 'Edit';
          $delete_label = 'Detach from template';

          # Get edit link
          $post_id = $item['ID'];
          $post_title = $item['post_title'];
          $edit_link = get_edit_post_link($post_id);

           # Build row actions
           $actions = array(
               'edit' => sprintf('<a href="%s">%s</a>', $edit_link, $edit_label),
           );
           if(!empty($template_slug) && $template_slug != 'default'){
             $actions['delete'] = sprintf('<a href="?page=%s&action=%s&template=%s&id=%s">%s</a>', $page_slug, 'detach', $template_slug, $post_id, $delete_label);
           }

           # Return the title column contents
           return sprintf('<a href="%1$s">%2$s</a> %3$s',
               /*$1%s*/ $edit_link,
               /*$2%s*/ $post_title,
               /*$3%s*/ $this->row_actions($actions)
           );

       }

       /////////////////////////////
       # Regsiter Bulk actions
       /////////////////////////////
       function get_bulk_actions() {

          # Get the current template
          $template_slug = (isset($_GET['template'])) ? sanitize_text_field($_GET['template']) : '';

          # Set the actions
          $actions = array();

          # Only allow bulk actions for no default templates
          if(!empty($template_slug) && $template_slug != 'default'){
            $actions['delete'] = 'Detach pages from template';
          }

          # Return the bulk actions
          return $actions;

       }

       /////////////////////////////
       # Process Bulk actions
       /////////////////////////////
       function process_bulk_action() {

        # Grab the selected ids
        global $wpdb;
        $ids = (isset($_REQUEST['ids'])) ? $_REQUEST['ids'] : '';

         # Loop through actions
         switch( $this->current_action() ){

           # Delete items
           case 'delete':

             # Verify template submission nonce
             $nonce = (isset($_POST['wpe_pti_table'])) ? sanitize_text_field($_POST['wpe_pti_table']) : '';
             // if( !wp_verify_nonce( $nonce, 'wpe_pti_table' ) ) return false;

             # Delete
             if(!empty($ids)){

               global $wpdb;

               # Sort array
               if(is_array($ids)) asort($ids);

               # Prepare ids for sql query
               // https://coderwall.com/p/zepnaw/sanitizing-queries-with-in-clauses-with-wpdb-on-wordpress
               $count_ids = count($ids);

               # Prepare the right amount of placeholders, if you're looking for strings, use '%s' instead
               $placeholders = array_fill(0, $count_ids, '%d');

               # Glue together all the placeholders...
               // $format = '%d, %d, %d, %d, %d, [...]'
               $format = implode(', ', $placeholders);

               # Setup sql query
               $sql = "
                 UPDATE {$wpdb->postmeta}
                 SET meta_value = 'default'
                 WHERE 1=1
                 AND post_id IN({$format})
                 AND meta_key = '_wp_page_template'
               ";

                # Update the data
                $prepared = $wpdb->prepare($sql, $ids);
                $wpdb->query($prepared);

               # Display the heading
               $ids = (is_array($ids)) ? implode(',', $ids) : $ids;
               echo '
                <div class="updated">
              			<p>The following post IDs have been deattached from this template: '.str_replace(',', ', ', $ids).'</a></p>
            		</div>
                ';

             }

           break;

         }

       }

       ///////////////////////
        # Render row cells
       ///////////////////////
       function column_default($item, $column_name){

           switch($column_name){

             /////////////
             # ID
             /////////////
             case 'id':
             case 'ID':
                 return $item['ID'];
                 break;

             /////////////
             # Thumbnail
             /////////////
             case 'thumbnail':
             case 'post_thumbnail':

                 # Return the thumbnail
                 $thumbnail_id = get_post_thumbnail_id( $item['ID'] );
                 // $thumbnail_src =  wp_get_attachment_url($thumbnail_id);
                 $thumbnail_src =  wp_get_attachment_thumb_url($thumbnail_id);
                 $display_thumbnail = (!empty($thumbnail_src)) ? '<img src="'.$thumbnail_src.'" width="60" />' : '';
                 return '<div class="thumb">'.$display_thumbnail.'</div>';
                 break;


             /////////////
             # Post Type
             /////////////
             case 'type':
             case 'post_type':
                 return $item['post_type'];
                 break;


             /////////////
             # Post Status
             /////////////
             case 'status':
             case 'post_status':
                 return $item['post_status'];
                 break;


             /////////////
             # Date
             /////////////
             case 'date':
               $date = $item['post_date'];
               // $return = mysql2date('d M Y', $date);
               $return = mysql2date('D F jS, Y', $date);
               $return .= ' <br /><small>' . mysql2date('g:ia', $date) . '</small>';
               return $return;
               break;

             /////////////
             # Default
             /////////////
             default:
                // Display the item array to debug unidentified objects
                return '<pre>' . print_r($item). '</pre>';
               break;

           }
       }


      //////////////////////////////////////
      # Append post types dropdown select
      //////////////////////////////////////
      // https://stackoverflow.com/questions/23859559/
      protected function extra_tablenav( $which ) {
        // keep empty
      }

     ////////////////////////
     # Get results
     ////////////////////////
     function prepare_items($args = array()) {

         # Handle bulk actions
         $this->process_bulk_action();

         # Get the template id
         $template_slug = (isset($args['template'])) ? $args['template'] : '';

         # Setup page parameters
         $current_page = $this->get_pagenum();
         $current_page = (isset($current_page)) ? $current_page : 1;
         $paged = (isset($_GET['page'])) ? sanitize_text_field($_GET['page']) : $current_page;
         $paged = (isset($_GET['paged'])) ? sanitize_text_field($_GET['paged']) : $current_page;
         $paged = (isset($args['paged'])) ? $args['paged'] : $paged;

         # Setup pagination parameters
         $per_page = (isset($args['per_page'])) ? $args['per_page'] : 10;
         $offset = ($paged - 1) * $per_page;

         # Setup order parameters
         $orderby = (isset($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
         $order = (isset($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc';

         # Setup table parameters
         $columns = $this->get_columns();
         $sortable = $this->get_sortable_columns();
         $hidden = array();

         # Generate headers
         $this->_column_headers = array($columns, $hidden, $sortable);

        # Setup sql query
        global $wpdb;
        $sql = "
          FROM {$wpdb->posts} AS _p
          INNER JOIN {$wpdb->postmeta} AS _pm
            ON (_p.ID = _pm.post_id)
          WHERE 1=1
          AND _pm.meta_key = '_wp_page_template'
          AND _pm.meta_value = %s
        ";
         $query_orderby = " ORDER BY {$orderby} {$order} ";
         $query_limit = " LIMIT {$per_page} OFFSET {$offset} ";

         # Count the total
         $prepared_total = $wpdb->prepare(" SELECT COUNT(*) " . $sql, $template_slug);
         $total = $wpdb->get_var($prepared_total);

         # Get the data
         $prepared = $wpdb->prepare(
             " SELECT * " . $sql . $query_orderby . $query_limit,
             $template_slug
         );
         $results = $wpdb->get_results($prepared);

         # Format result items and append to new array
         $new_results = array();
         if(!empty($results)){
           foreach($results as $k => $result){
             $new_results[] = (array) $result;
           }
         }
         $this->items = $new_results;

         # Set pagniation args
         $pagination_args = array(
             'total_items' => $total,
             'per_page'    => $per_page,
             'total_pages' => ceil($total/$per_page),
         );
         $this->set_pagination_args( $pagination_args );

       }

    }
?>
