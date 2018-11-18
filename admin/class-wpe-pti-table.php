<?php

/**
 * Admin list table for all post templates.
 *
 * @package    WPE_PTI
 * @subpackage WPE_PTI/admin
 * @author     Jeff Ward <hi@hello-jeff.com>
 *
 * Reference: https://wordpress.org/plugins/custom-list-table-example/
 */
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
				 'slug' => 'File location',
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

			# Get the selected post type
			$template_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';

			# Row action labels
			$view_label = 'View attached pages';
			$edit_label = 'Edit';
			$delete_label = 'Detach all pages';

			 # Build row actions
			 $actions = array(
					'view' => sprintf('<a href="?page=%s&template=%s&post_type=%s">%s</a>', $page_slug, $item['id'], $template_post_type, $view_label),
					// 'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">%s</a>', $page_slug, 'edit', $item['id'], $edit_label),
			 );

			 # Add delete row action
			 if(!empty($item['id']) && $item['id'] != 'default'){
				 $actions['delete'] = sprintf('<a href="?page=%s&action=%s&id=%s">%s</a>', $page_slug, 'delete', $item['id'], $delete_label);
			 }

			 # Return the title column contents
			 return sprintf('<a href="?page=%1$s&template=%2$s&post_type=%3$s">%4$s</a> %5$s',
					 /*$1%s*/ $page_slug,
					 /*$2%s*/ $item['id'],
					 /*$3%s*/ $template_post_type,
					 /*$4%s*/ $item['title'],
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



					 // comeback

					 // Delete logic goes here
					 // DELETE FROM ...

					 echo '
						<div class="updated">
							<p>'.sprintf(__('The following templates have been detached: %s'), $ids).'</a></p>
						</div>
						';
						print_r($ids);

				 }

			 break;

		 }

	 }

	 ///////////////////////
		# Render row cells
	 ///////////////////////
	 function column_default($item, $column_name){

			# Get the current post_type
			$template_post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'page';

			 switch($column_name){

				 /////////////////
				 # Path (slug)
				 /////////////////
				 case 'slug':
						 return $item['slug'];
						 break;

				 /////////////
				 # Count
				 /////////////
				 case 'count':

						# Get the current page
						$page_slug = (isset($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : '';

						# Get the count
						$count = $this->count_total_attached($item['slug'], $template_post_type);

						# Return the count
						return sprintf('<a href="?page=%s&template=%s&post_type=%s">%s</a>', $page_slug, $item['id'], $template_post_type, $count);

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

						# Setup ignored post types
						$ignore_types = array(
							'attachment'
						);

						# List the post types
						if(!empty($post_types)){
							echo '<select id="post-type-selector" name="set_post_type" style="float: none;">';
							foreach ( $post_types  as $post_type_obj ) {
								$post_type = $post_type_obj->name;
								if(!in_array($post_type, $ignore_types)){
									$post_type_label = $post_type_obj->label;
									$selected = ($selected_post_type == $post_type) ? 'selected="selected"' : '';
									echo '<option '.$selected.' value="'.$post_type.'">' . $post_type_label . '</p>';
								 }
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
	private function count_total_attached($template_slug = 'default', $post_type = null) {

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
