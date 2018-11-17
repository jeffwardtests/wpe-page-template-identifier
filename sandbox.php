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
  foreach ( $templates as $template_name => $template_filename ) {

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
?>
