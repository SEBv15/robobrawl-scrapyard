<span class="sya-label">Photos</span>
<div class="sya-photos">
    <?php
    foreach($item->images as $key => $image) {
        ?>
        <div class="sya-image" item_id="<?php echo $image->item_id; ?>" d_id="<?php echo $image->id; ?>" <?php /*pos="<?php echo $image->pos; ?>" */ ?>>
            <img class="sya-preview" src='<?php echo wp_get_attachment_url( $image->image_id ); ?>' height='100'>
            <span class="sya-del-img">X</span>
        </div>
        <?php
    }
    ?>
</div>
<?php /*<div class='image-preview-wrapper'>
    <img id='image-preview' src='<?php echo wp_get_attachment_url( get_option( 'scrapyard_attachment_id' ) ); ?>' height='100'>
</div> */ ?>
<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
<?php /*<input type='hidden' name='image_attachment_id' id='image_attachment_id' value='<?php echo get_option( 'scrapyard_attachment_id' ); ?>'>-->
<!--<input type="submit" name="submit_image_selector" value="Save" class="button-primary">--> */ ?>