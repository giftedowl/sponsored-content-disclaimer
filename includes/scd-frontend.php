<?php
// Prevent direct file access
if( ! defined( 'SCD_FILE' ) ) {
	die();
}



/* Display the SCD message before or after the content */
add_filter('the_content', scd_add_message);
function scd_add_message($content) {
  global $post;

  if ( is_single() ) {
    $scd_message = get_post_meta( $post->ID, 'scd-post-message', true );
    $scd_display = get_post_meta( $post->ID, 'scd-post-display', true );

    if ('' != $scd_message) {
      $wrapped_message = "<div class='scd-message-box'>" . $scd_message . "</div>";
      if('below' == $scd_display)
        $content = $content . $wrapped_message;
      else
        $content = $wrapped_message . $content;
    }
  }
  return $content;
}

add_action('wp_head', 'scd_inline_styles');

function scd_inline_styles() {

  $custom_css = get_option( 'scd-custom-css');

  if (!isset($custom_css) || $custom_css == '') {
    $custom_css = ".scd-message-box {
     background-color: #f2f2f2;
     padding: 10px;
     margin: 10px 0 10px 0;
     border: solid 1px #e2e2e2;
   }";
  }

  echo "<style type='text/css'>" . $custom_css . "</style>";
}
