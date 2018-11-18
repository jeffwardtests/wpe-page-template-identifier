<?php

/**
 * Admin list table for all post template children.
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
				 // 'id' => 'ID',
				 'thumbnail' => '',
				 'title' => 'Post Title',
				 'type' => 'Post Type',
				 'status' => 'Post Status',
				 'date' => 'Publish Date',
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
					 /*$2%s*/ $post_title . ' <small class="text-faded">(ID: '.$post_id.')</small>',
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
				$actions['delete'] = __('Detach pages from template');
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
								<p>'.sprintf(__('The following post IDs have been deattached from this template: %s'), str_replace(',', ', ', $ids)).'</a></p>
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

			# Set the post id
			$post_id = $item['ID'];

			 switch($column_name){

				 /////////////
				 # ID
				 /////////////
				 case 'id':
				 case 'ID':
						 return $post_id;
						 break;

				 /////////////
				 # Thumbnail
				 /////////////
				 case 'thumbnail':
				 case 'post_thumbnail':

						 $edit_link = get_edit_post_link($post_id);

						 # Return the thumbnail
						 $thumbnail_id = get_post_thumbnail_id( $post_id );
						 // $thumbnail_src =  wp_get_attachment_url($thumbnail_id);
						 $thumbnail_src =  wp_get_attachment_thumb_url($thumbnail_id);
						 $display_thumbnail = (!empty($thumbnail_src)) ? '<img src="'.$thumbnail_src.'" width="60" />' : '';

						 return '<a href="'.$edit_link.'"><div class="thumb">'.$display_thumbnail.'</div></a>';
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

		 # Setup post type
		 $post_type = (isset($_GET['post_type'])) ? sanitize_text_field($_GET['post_type']) : 'page';

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
				AND _p.post_type = %s
				AND _pm.meta_key = '_wp_page_template'
				AND _pm.meta_value = %s
		";
		 $query_orderby = " ORDER BY {$orderby} {$order} ";
		 $query_limit = " LIMIT {$per_page} OFFSET {$offset} ";

		 # Count the total
		 $prepared_total = $wpdb->prepare(" SELECT COUNT(*) " . $sql, $post_type, $template_slug);
		 $total = $wpdb->get_var($prepared_total);

		 # Get the data
		 $prepared = $wpdb->prepare(
				 " SELECT * " . $sql . $query_orderby . $query_limit,
				 $post_type,
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
