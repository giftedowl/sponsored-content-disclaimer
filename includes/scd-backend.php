<?php
// Prevent direct file access
if( ! defined( 'SCD_FILE' ) ) {
	die();
}

/**
 * Register settings
 *
 * @since 1.0
 */
function scd_register_settings() {
	register_setting( 'scd_settings', 'scd-custom-css' );
}
add_action( 'admin_init', 'scd_register_settings' );


/**
 * Register "Custom CSS" submenu in "Appearance" Admin Menu
 *
 * @since 1.0
 */
function scd_register_submenu_page() {
	add_theme_page( __( 'Sponsored Content Styling', 'sponsored-content-disclaimer' ), __( 'Sponsored Content', 'sponsored-content-disclaimer' ), 'edit_theme_options', basename( SCD_FILE ), 'scd_render_submenu_page' );
}
add_action( 'admin_menu', 'scd_register_submenu_page' );

/**
 * Render Admin Menu page
 *
 * @since 1.0
 */
function scd_render_submenu_page() {

	// $option = get_option( 'scd-custom-css' );
	// $content = isset( $option ) && ! empty( $option ) ? $option : __( '/* Enter Your Custom CSS Here */', 'sponsored-content-disclaimer' );

	if ( isset( $_GET['settings-updated'] ) ) : ?>
		<div id="message" class="updated"><p><?php _e( 'Custom CSS updated successfully.', 'sponsored-content-disclaimer' ); ?></p></div>
	<?php endif; ?>

	<div class="wrap">
		<h2 style="margin-bottom: 1em;"><?php _e( 'Sponsored Content Styling', 'scd-styling' ); ?></h2>
    <div id="example">
      <h3>Default Example</h3>
      <textarea cols="50" rows="10" disabled="true">.scd-message-box {
        background-color: #f2f2f2;
        padding: 10px;
         margin: 10px 0 10px 0;
        border: solid 1px #e2e2e2;
}
        </textarea>
    </div>
    <h3>Enter your custom css here</h3>
		<form name="scd-form" action="options.php" method="post" enctype="multipart/form-data">
      <?php settings_fields( 'scd_settings' ); ?>
      <?php do_settings_sections( 'scd_settings' ); ?>
			<div id="template">
				<div>
					<textarea cols="10" rows="10" name="scd-custom-css" id="scd-custom-css" ><?php echo esc_html( get_option( 'scd-custom-css' ) ); ?></textarea>
				</div>
				<div>
					<?php submit_button( __( 'Update Sponsored Content CSS', 'sponsored-content-disclaimer' ), 'primary', 'submit', true ); ?>
				</div>
			</div>
		</form>
	</div>
<?php
}


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
