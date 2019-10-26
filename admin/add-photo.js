jQuery(document).ready(function ($) {

    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    //var set_to_post_id = < ? php echo $my_saved_attachment_post_id; ? > ; // Set this

    jQuery('#upload_image_button').on('click', function (event) {

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            // Set the post ID to what we want
            //file_frame.uploader.uploader.param('post_id', set_to_post_id);
            // Open frame
            file_frame.open();
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            //wp.media.model.settings.post.id = set_to_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select an image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();

            // Do something with attachment.id and/or attachment.url here
            //$('#image-preview').attr('src', attachment.url).css('width', 'auto');
            //$('#image_attachment_id').val(attachment.id);
            var list = $(".sya-photos")
            var img = $(`
            <div class="sya-image" item_id="" d_id="">
                <img class="sya-preview" src='' height='100'>
                <span class="sya-del-img">X</span>
            </div>`);
            img.find("img").attr("src", attachment.url);
            img.attr("item_id", $("input[name=sy_item_id").val())

            $.post("/wp-json/scrapyard/v1/addPhoto/"+img.attr("item_id")+"/"+attachment.id, function(res) {
                img.attr("d_id", res);
            });

            img.on("click", imageReorder)
            img.find("span.sya-del-img").click(function() {
                imageDelete(img)
            })
            img.appendTo(list);

            // Restore the main post ID
            wp.media.model.settings.post.id = wp_media_post_id;
        });

        // Finally, open the modal
        file_frame.open();
    });

    // Restore the main ID when the add media button is pressed
    jQuery('a.add_media').on('click', function () {
        wp.media.model.settings.post.id = wp_media_post_id;
    });

    $("div.sya-image").each(function() {
        $(this).on("click", imageReorder)
        var that = this;
        $(this).find("span.sya-del-img").click(function() {
            imageDelete(that)
        })
    })
    function imageReorder() {
        $.post("/wp-json/scrapyard/v1/photoToStart/"+$(this).attr("item_id")+"/"+$("div.sya-image").index(this))
        $(this).prependTo($("div.sya-photos"))
    }
    function imageDelete(that) {
        $.post("/wp-json/scrapyard/v1/removeImg/"+$(that).attr("d_id"))
        $(that).remove();
    }
});