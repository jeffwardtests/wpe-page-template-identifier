<?php


/////////////////////
# Add admin menu
/////////////////////
add_action('admin_menu', 'add_wpe_pti_admin');
function add_wpe_pti_admin(){
  add_menu_page('WPE Page Templates Identifier', 'Page Templates', 'activate_plugins', 'contact_form_submissions', 'render_wpe_pti_admin', 'dashicons-welcome-widgets-menus');
}

////////////////////////
# Render admin page
////////////////////////
function render_wpe_pti_admin(){

  //////////////////////////
  # Get all post types
  //////////////////////////
  echo '<h3>All post types</h3>';
  $args = array(
     'public'   => true,
     '_builtin' => false
  );
  $output = 'names'; // names or objects, note names is the default
  $operator = 'and'; // 'and' or 'or'
  $post_types = get_post_types( $args, $output, $operator );
  foreach ( $post_types  as $post_type ) {
     echo '<p>' . $post_type . '</p>';
  }

  //////////////////////////
  # Get all page templates
  //////////////////////////
  echo '<hr />';

  echo '<h3>All templates</h3>';

  $templates = get_page_templates( $current_post = null, $template_post_type = 'page' );
  foreach ( $templates as $template_name => $template_filename ) {
    echo "$template_name ($template_filename)<br />";
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

        echo '<pre>';
        print_r($results);
        echo '</pre>';
        echo '<br> Title: ' . $post->post_title;
        echo '<br> Template: ' . $page_template;

    }
  }
  echo '<hr />';

}


?>
