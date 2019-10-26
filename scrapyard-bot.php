<?php 

global $scrapyard_bot_title;
$scrapyard_bot_title = "T";

if(isset($_GET["bot_id"])) {
    $bot = scrapyard_get_item($_GET["bot_id"]);
    $scrapyard_bot_title = $bot->name;
} else {
    $scrapyard_bot_title = "Bot Not Found";
}

add_filter( 'get_the_archive_title', 'wp_title_so_18381106' );
function wp_title_so_18381106( $oldtitle) {
    global $scrapyard_bot_title;
    return $scrapyard_bot_title;
}

get_header(); 
?>
<div class="page no-sidebar">
    <div class="content-area" id="primary">
        <?php if(count($bot->images) != 0) { ?>
        <div class="syb-gallery">
            <?php
            foreach($bot->images as $key => $image) {
                ?>
                <div class="syb-image-container">
                    <div class="syb-image" style="background-image: url(<?php echo wp_get_attachment_url( $image->image_id ); ?>)"></div>
                </div>
                <?php
            } ?>
        </div>
        <?php } ?>
        <p class="syb-description">
            <?php echo $bot->description; ?>
        </p>
        <table>
            <?php
            foreach($bot->attributes as $key=>$attr) {
                echo "<tr>";
                echo "<td>".$attr->name."</td>";
                echo "<td>".$attr->value."</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <?php echo $bot->extra_html ?>
    </div>
</div>
<?php
get_sidebar();
get_footer();
?>