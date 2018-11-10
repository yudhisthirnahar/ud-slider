<?php
/**
 * Plugin Name: Ud Slider
 * Plugin URI: https://github.com/yudhisthirnahar/ud-slider
 * Description: Image Slider
 * Version: 1.0
 * Author: Yudhisthir Nahar
 * Author URI: https://github.com/yudhisthirnahar
 *
 * @package Ud slider
 */

/**
 * Ud_slider_options_group.
 */
function ud_slider_register_settings() {
	add_option( 'ud_slider_option_name', 'This is Ud option value.' );
	register_setting( 'ud_slider_options_group', 'ud_slider_option_name', 'ud_slider_callback' );
}

add_action( 'admin_init', 'ud_slider_register_settings' );

/**
 * Options or settings page.
 */
function ud_slider_register_options_page() {
	add_options_page( 'Ud slider', 'Ud slider settings', 'manage_options', 'ud_slider', 'ud_slider_options_page' );
}

add_action( 'admin_menu', 'ud_slider_register_options_page' );

/**
 * Check to make sure its a successful upload.
 *
 * @param file_handler $file_handler File handler name.
 * @param int          $post_id The post id.
 * @param bool         $set_thu default value is false.
 */
function ud_handle_attachment( $file_handler, $post_id, $set_thu = false ) {
	if ( ! empty( $_FILES[ $file_handler ]['error'] ) ) {

			return false;

	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attach_id = media_handle_upload( $file_handler, $post_id );

	// If you want to set a featured image frmo your uploads.
	if ( $set_thu ) {
		set_post_thumbnail( $post_id, $attach_id );
	}
	return $attach_id;
}

/**
 * Save Uploads Or Options Settings.
 */
function ud_slider_options_page() {

	wp_enqueue_style( 'jquery-ui-style', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css', array( 'jquery' ), '1.0', false );
	wp_enqueue_script( 'jquery-ui-core', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js', array( 'jquery' ), '1.0', false );
	$ud_uploaded_files = array();
	if ( ! empty( get_option( 'ud_slider_images' ) ) ) {
		$ud_uploaded_files = get_option( 'ud_slider_images' );
	}
	if ( ! empty( $_FILES['ud_slider_images'] ) ) {
		$files['name']     = array_map( 'sanitize_text_field', wp_unslash( $_FILES['ud_slider_images']['name'] ) );
		$files['type']     = array_map( 'sanitize_text_field', wp_unslash( $_FILES['ud_slider_images']['type'] ) );
		$files['tmp_name'] = array_map( 'sanitize_text_field', $_FILES['ud_slider_images']['tmp_name'] );
		$files['error']    = array_map( 'sanitize_text_field', wp_unslash( $_FILES['ud_slider_images']['error'] ) );
		$files['size']     = array_map( 'sanitize_text_field', wp_unslash( $_FILES['ud_slider_images']['size'] ) );
		if ( ! empty( $files ) ) {
			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ] ) {
					$file        = array(
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ],
					);
					$ud_images[] = $file;

					foreach ( $ud_images as $uploadedfile ) {
						$upload_overrides = array( 'test_form' => false );
						$movefile         = wp_handle_upload( $uploadedfile, $upload_overrides );

						if ( $movefile && ! isset( $movefile['error'] ) ) {

							$ud_uploaded_files[] = $movefile['url'];
						}
					}
				}
			}
			if ( count( $ud_uploaded_files ) ) {
				update_option( 'ud_slider_images', $ud_uploaded_files );
			}
		}
	} elseif ( ! empty( $_POST['ud_slider_nonce_field'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ud_slider_nonce_field'] ) ), 'ud_slider_save' ) && ! empty( $_POST['ud_slider_images_options'] ) ) {

			$ud_slider_images  = sanitize_text_field( $_POST['ud_slider_images_options'] );
			$ud_uploaded_files = json_decode( stripslashes( $ud_slider_images ), true );
			update_option( 'ud_slider_images', $ud_uploaded_files );
	}
	?>
	<div class='ud_slider_settings' style="padding-bottom: 22px;border-bottom: 2px dotted;margin-bottom: 20px;">       
		<h2>Ud Slider Settings</h2>
		<div class='ud_slider_upload_images'>
		<form action='' method='post' enctype='multipart/form-data' name='ud_slider_upload' >
	<?php settings_fields( 'ud_slider_options_group' ); ?>
	<?php do_settings_sections( 'ud_slider_options_group' ); ?>
			<label> Upload Slider Images here :<input type='file' name='ud_slider_images[]'  multiple='multiple' accept='image/*'></label>
			<input type='submit' name='Upload'>
		</form>
		</div>
	</div>
	<div id="uploaded_images">
		<h3>Drag & Drop To Reorder Slides : </h3>        
		<ul id="uploaded_images_sortable" style='display: inline-block;'>
	<?php
	if ( ! empty( $ud_uploaded_files ) && count( $ud_uploaded_files ) ) {
		foreach ( $ud_uploaded_files as $key => $images_url ) {
			?>
			<li style="display:table" class="ui-state-default" for_index="<?php echo esc_html( $key + 1 ); ?>" for_url='<?php echo esc_url( $images_url ); ?>'><img class="ud_slider_images" style="display:inline-block;width: 300px; height: 200px;" src ="<?php echo esc_url( $images_url ); ?>" ><a href="javascript:void(0);" style="font-size: 15px;background: black;color:white;display:block;padding:10px" class="delete_slide">Delete</a></li>
				<?php
		}
	}
	?>
		</ul>        
	</div>
	<div style="margin-top: 70px;">
		<form action='' method='post' name='frm_ud_slider_save' >
	<?php wp_nonce_field( 'ud_slider_save', 'ud_slider_nonce_field' ); ?>		
		<input name = 'ud_slider_images_options' id = 'ud_slider_images_options' type='hidden' value="">
		<input type='submit' id='ud_slider_save' name='ud_slider_save' value='Save Changes'>
		</form>
	</div>


	<?php
	echo '<style type="text/css">.hide {display:none;} #uploaded_images_sortable { list-style-type: none; margin: 0; padding: 0; width: 1000px; }
	#uploaded_images_sortable li { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 300px; height: 200px; font-size: 4em; text-align: center; margin-bottom: 40px;
    margin-right: 20px; }</style>';
	wp_register_script( 'ud-slider-custom-script', plugin_dir_url( __FILE__ ) . 'customjs.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'ud-slider-custom-script' );
}

/**
 * Add bxslider js and cs.
 */
function ud_slider_includes() {
	wp_enqueue_style( 'ud-slider-style', 'https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css', array(), '4.2.12' );
	wp_enqueue_script( 'ud-slider-bxslider', 'https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js', array(), '4.2.12', true );
}

add_action( 'wp_footer', 'ud_slider_includes' );

/**
 * Create slider shortcode.
 */
function ud_slider_shortcode() {
	ob_start();
	$udsliderimages = get_option( 'ud_slider_images' );
	if ( empty( $udsliderimages ) ) {
		return ob_get_clean();
	}
	?>
	<div class="ud-slider">
	<?php
	foreach ( $udsliderimages as $images ) {
		?>
		<div><img style="margin:0px auto;" src = "<?php echo esc_url( $images ); ?>" /></div>
		<?php
	}
	?>
	</div>
	<?php

	do_action( 'ud_slider_includes' );

	echo (
		'<script>jQuery(document).ready(function ($) {      
            jQuery(".ud-slider").bxSlider();
    });</script>'
	);

	return ob_get_clean();
}

add_shortcode( 'ud-slider', 'ud_slider_shortcode' );

?>
