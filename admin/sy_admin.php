<?php
add_action("admin_init", 'scrapyard_register_admin_script');
add_action( 'admin_menu', 'register_scrapyard_settings_page' );

function scrapyard_register_admin_script() {
	wp_register_script( 'scrapyard-admin-script', plugins_url('/admin-script.js', __FILE__) );
	wp_register_script("sy_autocomplete",plugins_url('../autocomplete.js', __FILE__));
	wp_register_script("sy_add_photo",plugins_url('add-photo.js', __FILE__));
}
function scrapyard_enqueue_admin_script() {
	wp_enqueue_script("sy_autocomplete");
	wp_enqueue_script( 'scrapyard-admin-script' );
	wp_enqueue_script( 'sy_add_photo' );
}

function register_scrapyard_settings_page() {
	$page = add_menu_page( 'Scrapyard', 'Scrapyard', 'manage_options', 'scrapyard', 'scrapyard_settings_page_callback' );
	add_action('admin_print_scripts-' . $page, 'scrapyard_enqueue_admin_script');
}

function scrapyard_settings_page_callback() {

	// Save attachment ID
	/*if ( isset( $_POST['submit_image_selector'] ) && isset( $_POST['image_attachment_id'] ) ) :
		update_option( 'scrapyard_attachment_id', absint( $_POST['image_attachment_id'] ) );
	endif;*/

	wp_enqueue_media();

	// AJAX OPERATIONS
	if(isset($_POST["sya_action"])) {
		if($_POST["sya_action"] == "del-attribute") {
			scrapyard_delete_attr($_GET["sy_item_id"], $_POST["sya_del_attr_name"]);
			return;
		}
		if($_POST["sya_action"] == "add-attribute") {
			scrapyard_upsert_attr($_GET["sy_item_id"], stripslashes($_POST["sya_add_attr_name"]), stripslashes($_POST["sya_add_attr_value"]));
			return;
		}
		if($_POST["sya_action"] == "delete") {
			scrapyard_delete_item($_GET["sy_item_id"]);
			return;
		}
	}

	// HANDLE FORM SUBMISSION
	if(isset($_POST["sy_item_id"])) {
		$item_id = $_POST["sy_item_id"];
		// name, description, html
		scrapyard_edit_item($item_id, stripslashes($_POST["sy_name"]), stripslashes($_POST["sy_description"]), stripslashes($_POST["sy_extra-html"]));

		// Attributes
		foreach($_POST as $key => $value) {
			if(strpos($key, 'sy_attr-') === 0) {
				scrapyard_upsert_attr($item_id, str_replace("_", " ", str_replace("sy_attr-", "", $key)), stripslashes($value));
			}
		}
	}

	?>
	<div class="sya-content">
	<?php
	if(!isset($_GET["sy_item_id"])) {
		?>
		<h1>Scrapyard</h1>
		<p>Click to edit</p>
		<?php

		//scrapyard_add_item("Test");
		//scrapyard_upsert_attr(1, "hi", "test!");
		//scrapyard_insert_img(0, 11);
		//scrapyard_reorder_img(0, 0, 3);
		$item_ids = scrapyard_get_item_ids();
		foreach($item_ids as $key => $item_id) {
			$data = scrapyard_get_item($item_id);
			?>
			<div class="sya_bot" item_id="<?php echo $item_id ?>">
				<img class="sya_main_img" src='<?php echo wp_get_attachment_url( $data->images[0]->image_id ); ?>'>
				<span class="sya_title"><?php echo $data->name ?></span>
				<p class="sya_cardcode">[sy_card id="<?php echo $item_id ?>"<span> name="<?php echo $data->name ?>"</span>]</p>
			</div>
			<?php
		} 
		?>

		<a href="?page=scrapyard&sy_item_id=new"><button class="button">Add Bot</button></a>

		<?php

		//echo '<pre>' . var_export(scrapyard_get_item(scrapyard_get_item_ids()[0]), true) . '</pre>';

	} else {
		$item_id = $_GET["sy_item_id"];
		if ($_GET["sy_item_id"] == "new") {
			$mysql_id = scrapyard_add_item("New Bot", "");
			$item_id = intval(get_option("scrapyard_item_counter")) - 1;
		}
		$item = scrapyard_get_item($item_id);

		echo '<h1>'.$item->name.'</h1>';
		?>
		<div class="sya-edit-item">
			<form class="sya-edit-item" method="post" id="sya-form" action="?page=scrapyard">
				<label for="syaf-name">Name</label>
				<input type="text" id="syaf-name" name="sy_name" placeholder="Name" value="<?php echo $item->name; ?>">
				<?php include "photo_mngr.php"; ?>
				<label for="syaf-description">Description</label>
				<textarea id="syaf-description" name="sy_description" form="sya-form" class="sya-description"><?php echo $item->description; ?></textarea>
				<span class="sya-label">Attributes</span>
				<div class="sya-attributes">
				<div class="sya-add-attribute">
					<input type="text" placeholder="Name" onkeydown="return event.key != 'Enter';" class="add-attribute-name">
					<input type="text" placeholder="Value" onkeydown="return event.key != 'Enter';" class="add-attribute-value">
					<button class="button">Add</button>
				</div>
				<?php
				foreach($item->attributes as $key => $attribute) {
					?>
					<div class="sya-attribute">
						<span title="<?php echo $attribute->name; ?>"><?php echo $attribute->name; ?></span>
						<div class="sya_autocomplete">
							<input type="text" autocomplete="off" name="sy_attr-<?php echo strtolower($attribute->name); ?>" value="<?php echo $attribute->value?>">
						</div>
						<button class="button sya-delete-attr">Delete</button>
					</div>
					<?php
				}
				?>
				</div>
				<label for="syaf-extra-html">Extra HTML</label>
				<textarea id="syaf-extra-html" name="sy_extra-html" form="sya-form" class="sya-extra-html"><?php echo $item->extra_html; ?></textarea>
				<input type="hidden" name="sy_item_id" value="<?php echo $item_id; ?>">
				<div class="sya-buttons">
					<button class="sya-delete-item button">Delete</button>
					<div class="sya-spacer"></div>
					<input type="submit" value="Save" class="button-primary sya-submit">
				</div>
			</form>
		</div>
	<?php } ?>
	<div class="sya-block"></div>
	
	<?php /*<form method='post'>
		<div class='image-preview-wrapper'>
			<img id='image-preview' src='<?php echo wp_get_attachment_url( get_option( 'scrapyard_attachment_id' ) ); ?>' height='100'>
		</div>
		<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
		<input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'scrapyard_attachment_id' ); ?>'>
		<input type="submit" name="submit_image_selector" value="Save" class="button-primary">
	</form> */ ?>

	</div>
	<?php

}

// only allow people to access page if the current user on the admin page hasn't pinged in over 5 seconds
// admin page will set cookie with a unique generated id for only the browser session
add_action( 'rest_api_init', function () {
	register_rest_route( 'scrapyard/v1', '/editing/(?P<id>[a-zA-Z0-9-]+)', array(
		'methods' => 'GET',
		'callback' => 'sya_currently_editing',
	) );
	register_rest_route( 'scrapyard/v1', '/getItem/(?P<id>[a-zA-Z0-9-]+)', array(
		'methods' => 'GET',
		'callback' => 'sya_rest_getItem',
	) );
	register_rest_route( 'scrapyard/v1', '/addPhoto/(?P<id>[a-zA-Z0-9-]+)/(?P<image_id>[a-zA-Z0-9-]+)', array(
		'methods' => 'POST',
		'callback' => 'sya_add_photo'
	));
	register_rest_route( 'scrapyard/v1', '/photoToStart/(?P<id>[a-zA-Z0-9-]+)/(?P<index>[a-zA-Z0-9-]+)', array(
		'methods' => 'POST',
		'callback' => 'sya_photo_to_start'
	));
	register_rest_route( 'scrapyard/v1', '/removeImg/(?P<id>[a-zA-Z0-9-]+)', array(
		'methods' => 'POST',
		'callback' => 'sya_remove_img'
	));
	register_rest_route( 'scrapyard/v1', '/types', array(
		'methods' => 'GET',
		'callback' => 'sya_get_types'
	));
} );
function sya_currently_editing(WP_REST_REQUEST $request) {
	$id = $request["id"];
	$curr_id = get_option("scrapyard_editing_id");
	$edit_time = get_option("scrapyard_editing_time");
	if($id == $curr_id) {
		update_option("scrapyard_editing_time", time());
		return 'ok';
	} else {
		if(time() - 5 > $edit_time) {
			update_option("scrapyard_editing_id", $id);
			update_option("scrapyard_editing_time", time());
			return 'ok';
		} else {
			return "denied";
		}
	}
}
function sya_rest_getItem(WP_REST_REQUEST $request) {
	$item_id = $request["id"];
	return scrapyard_get_item($item_id);
}
function sya_add_photo(WP_REST_REQUEST $request) {
	$item_id = $request["id"];
	$image_id = $request["image_id"];
	return scrapyard_insert_img($item_id, $image_id);
}
function sya_photo_to_start(WP_REST_REQUEST $request) {
	$item_id = $request["id"];
	$index = $request["index"];
	scrapyard_reorder_img($item_id, $index, 0);
	return 0;
}
function sya_remove_img(WP_REST_REQUEST $request) {
	$id = $request["id"];
	scrapyard_delete_img($id);
	return true;
}
function sya_get_types(WP_REST_REQUEST $request) {
	return scrapyard_get_types();
}


//add_action( 'admin_footer', 'scrapyard_print_scripts' );

function scrapyard_print_scripts() {

	$my_saved_attachment_post_id = get_option( 'scrapyard_attachment_id', 0 );

	?><script type='text/javascript'>

		jQuery( document ).ready( function( $ ) {

			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this

			jQuery('#upload_image_button').on('click', function( event ){

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});

				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();

					// Do something with attachment.id and/or attachment.url here
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#image_attachment_id' ).val( attachment.id );

					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});

					// Finally, open the modal
					file_frame.open();
			});

			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		});

	</script>
	

	<?php

}

function scrapyad_load_admin_style() {
    $plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'sya', $plugin_url . 'admin-style.css' );
}
add_action( 'admin_enqueue_scripts', 'scrapyad_load_admin_style' );