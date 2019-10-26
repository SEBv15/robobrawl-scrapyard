<?php
/**
 * I don't really know how this exactly works, but basically when
 * the URL matches 'scrapyard-bot*', the request gets intercepted
 * and 'bot-page.php' will be shown instead.
 */
add_action( 'init', 'syp_init_internal' );
function syp_init_internal()
{
    add_rewrite_rule( 'scrapyard-bot$', 'index.php?syp_api=1', 'top' );
}

add_filter( 'query_vars', 'syp_query_vars' );
function syp_query_vars( $query_vars )
{
    $query_vars[] = 'syp_api';
    return $query_vars;
}
add_action( 'parse_request', 'syp_parse_request' );
function syp_parse_request( &$wp )
{
    if ( array_key_exists( 'syp_api', $wp->query_vars ) ) {
        // Add style and scripts for bot pages
        add_action("wp_enqueue_scripts", "syb_add_style");
        global $syb_add_style;
        function syb_add_style() {
            $plugin_url = plugin_dir_url( __FILE__ );
            wp_enqueue_style( 'sy_bot_page', $plugin_url . 'bot-page.css' );
            wp_enqueue_style("sy_slick_style", "//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css");
            wp_enqueue_script("sy_slick", "//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js");
            wp_enqueue_script("sy_bot_page", $plugin_url . 'bot-page.js');
        }
        include 'bot-page.php';
        exit();
    }
    return;
}
?>