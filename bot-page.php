<?php
function sy_bot_page_shortcode_init() {
    function scrapyard_bot() {
        update_option("scrapyard_bot_page", $_SERVER["REQUEST_URI"]);
        if(isset($_GET["bot_id"])) {
            $bot = scrapyard_get_item($_GET["bot_id"]);
            ?>
            <h1><?php echo $bot->name; ?></h1>
            <p><?php echo $bot->description ?></p>
            <?php
        } else {
        ?>
            <h1>This Page Doesn't Exist</h1>
        <?php
        }
    }
    add_shortcode("scrapyard_bot", "scrapyard_bot");
}

add_action('init', 'sy_bot_page_shortcode_init');
?>