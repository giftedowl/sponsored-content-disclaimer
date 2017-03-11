<?php
/*
Plugin Name: Sponsored Content Disclaimer
Plugin URI: https://github.com/citymomsblog/sponsored-content-disclaimer
Description: Add a custom disclaimer message to any blog post. The message can be displayed either above or below the content. Add custom CSS to provide a consistent style across your blog.
Version: 1.0
Author: City Moms Blog Network
Author URI: http://www.citymomsblog.com
License: GPLv2 or later
*/

// don't load directly
if (!defined('ABSPATH')) die('-1');

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'scd_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'scd_post_meta_boxes_setup' );

/* Meta box setup function. */
function scd_post_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'scd_add_post_meta_boxes' );

  /* Save post meta on the 'save_post' hook. */
  add_action( 'save_post', 'scd_save_post_class_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function scd_add_post_meta_boxes() {

  add_meta_box(
    'scd-post-class',      // Unique ID
    'Sponsored Content Disclaimer',    // Title
    'scd_post_class_meta_box',   // Callback function
    'post',         // Admin page (or post type)
    'normal'         // Context
    );
}

/* Display the post meta box. */
function scd_post_class_meta_box( $object, $box ) {

  wp_nonce_field( basename( __FILE__ ), 'scd-post-class-nonce' );

  ?>
  <textarea name="scd-post-message" id="scd-post-message"
    style="width:100%;"><?php echo esc_attr( get_post_meta( $object->ID, 'scd-post-message', true ) ); ?></textarea> <br/>

 <?php
    $post_meta_display = get_post_meta($object->ID, 'scd-post-display', true);
  ?>
  <label for="scd-post-class"><?php _e( "Display:", 'example' ); ?></label><br/>
  <input type="radio" name="scd-post-display" value="above"
    <?php checked( 'above', $post_meta_display ); ?> />Above Post <br/>
  <input type="radio" name="scd-post-display" value="below"
    <?php checked( 'below', $post_meta_display ); ?> />Below Post <br />

    <?php
}

/* Save the meta box's post metadata. */
function scd_save_post_class_meta( $post_id, $post ) {

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['scd-post-class-nonce'] ) || !wp_verify_nonce( $_POST['scd-post-class-nonce'], basename( __FILE__ ) ) )
    return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_scd_message = ( isset( $_POST['scd-post-message'] ) ? $_POST['scd-post-message'] : '' );
  $new_scd_display = ( isset( $_POST['scd-post-display'] ) ? $_POST['scd-post-display'] : '' );

  /* Get the meta value of the custom field key. */
  $old_scd_message = get_post_meta( $post_id, 'scd-post-message', true );
  $old_scd_display = get_post_meta( $post_id, 'scd-post-display', true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_scd_message && '' == $old_scd_message ) {
    add_post_meta( $post_id, 'scd-post-message', $new_scd_message, true );
    add_post_meta( $post_id, 'scd-post-display', $new_scd_display, true );
  }
  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_scd_message ) {

    if ($new_scd_message != $old_scd_message)
      update_post_meta( $post_id, 'scd-post-message', $new_scd_message );

    if ($new_scd_display != $old_scd_display)
      update_post_meta( $post_id, 'scd-post-display', $new_scd_display );
  }

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( '' == $new_scd_message && $old_scd_message ) {
    delete_post_meta( $post_id, 'scd-post-message', $old_scd_message );
    delete_post_meta( $post_id, 'scd-post-display', $old_scd_display );
  }
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
