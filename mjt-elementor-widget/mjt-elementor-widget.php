<?php
/**
 * Plugin Name: Elementor Event Widget
 * Description: Elementor widget for displaying events with location and date.
 * Author: Madhavi Jha
 * Author URI: https://github.com/madhavijha01
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function mjt_register_event_cpt() {
    $labels = array(
        'name'               => 'Events',
        'singular_name'      => 'Event',
        'add_new'            => 'Add New Event',
        'add_new_item'       => 'Add New Event',
        'edit_item'          => 'Edit Event',
        'new_item'           => 'New Event',
        'view_item'          => 'View Event',
        'search_items'       => 'Search Events',
        'not_found'          => 'No events found',
        'not_found_in_trash' => 'No events found in Trash',
        'menu_name'          => 'Events',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_rest'       => true,
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
    );

    register_post_type('event', $args);
}
add_action('init', 'mjt_register_event_cpt');

function mjt_meta_box_for_event_callback($post){
	 wp_nonce_field(basename(__FILE__), "mjt_event_nonce");
	$mjt_location_address = get_post_meta( $post->ID , '_mjt_location_address', true );?>
	<p>
		<label for="location_address"> <?php _e( 'Event&apos;s Location', 'mjt_location_address' ) ; ?><br/>
			<textarea name="mjt_location_address" id="mjt_location_address" rows="4" col="50"><?php echo  $mjt_location_address ;?></textarea>    
		</label>
	</p> 
<?php } 

// Meta box callback function
function mjt_picker_meta_box_callback( $post ) {
   //  wp_nonce_field( 'mjt_picker_nonce', 'mjt_picker_nonce' );
    $value = get_post_meta( $post->ID, '_mjt_picker_meta_value', true );

    echo '<label for="mjt_picker_field">Date and Time:</label><br>';
    echo '<input type="text" id="mjt_picker_field" name="mjt_picker_field" value="' . esc_attr( $value ) . '" style="width:100%;" />';
}

function mjt_event_add_custom_meta_box() {  

	 add_meta_box('mjt_address_meta_box', __('Event&apos;s Location', 'mjtevent-meta'), 'mjt_meta_box_for_event_callback', 'event', 'normal', 'high' );
	 
	 add_meta_box(
        'mjt_picker_meta_box',
        'Date and Time',
        'mjt_picker_meta_box_callback',
        'event', // Change 'post' to other post types if needed
        'normal',
        'high'
    );
   
}
add_action('add_meta_boxes', 'mjt_event_add_custom_meta_box');

function save_mjt_event_meta_box( $post_id ) {
  // Checks save status
 $is_autosave = wp_is_post_autosave( $post_id );
 $is_revision = wp_is_post_revision( $post_id );
 $is_valid_nonce = ( isset( $_POST[ 'mjt_event_nonce' ] ) && wp_verify_nonce( $_POST[ 'mjt_event_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 // Exits script depending on save status
   if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
       return;
   }  

    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['mjt_picker_field'] ) ) {
        update_post_meta( $post_id, '_mjt_picker_meta_value', sanitize_text_field( $_POST['mjt_picker_field'] ) );
    }
 if( isset( $_POST[ 'mjt_location_address' ] ) ) {
	  $mjt_location_address = $_POST[ 'mjt_location_address' ] ;
      update_post_meta( $post_id, '_mjt_location_address', $mjt_location_address );
  }
  
  
   
}

add_action('save_post', 'save_mjt_event_meta_box', 10, 2);


// Enqueue necessary scripts and styles
function dt_picker_enqueue_scripts() {
    wp_enqueue_style( 'jquery-ui-datepicker', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1', 'all' );
    wp_enqueue_script( 'jquery-ui-datepicker', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array( 'jquery' ), '1.12.1', true );

    // Enqueue a custom script for timepicker functionality (using timepicker addon)
    wp_enqueue_script( 'jquery-ui-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', array('jquery', 'jquery-ui-datepicker'), '1.6.3', true );
    wp_enqueue_style( 'jquery-ui-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css', array('jquery-ui-datepicker'), '1.6.3', 'all');

    wp_enqueue_script( 'dt-picker-script', plugins_url( 'assets/js/dt-picker.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-timepicker-addon' ), '1.0', true );
}

add_action( 'admin_enqueue_scripts', 'dt_picker_enqueue_scripts' );




// https://developers.elementor.com/docs/scripts-styles/frontend-styles/
function my_plugin_frontend_stylesheets() {

	wp_register_style( 'event-mjt', plugins_url( 'assets/css/event-mjt.css', __FILE__ ) );
	wp_enqueue_style( 'event-mjt' );	

}
add_action( 'elementor/frontend/after_enqueue_styles', 'my_plugin_frontend_stylesheets' );

function register_event_widget( $widgets_manager ) {
    require_once( __DIR__ . '\widgets\event-widget.php' );
    $widgets_manager->register( new \Elementor\Event_Widget() );
}
add_action( 'elementor/widgets/register', 'register_event_widget' );
