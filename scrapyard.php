<?php
/**
 * Plugin Name: Scrapyard
 * Description: Dead Robots!
 * Version: 1.0
 * Author: Sebastian Strempfer
 */

include_once("cards.php");
include_once("admin/sy_admin.php");
include_once("bot-page-init.php");
include_once("bot-page.php");

function sy_load_plugin_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'sy', $plugin_url . 'style.css' );
    wp_enqueue_script('sy', $plugin_url . 'script.js', array('jquery'));
}
add_action( 'wp_enqueue_scripts', 'sy_load_plugin_scripts' );

include_once('db.php');
register_activation_hook( __FILE__, 'scrapyard_install' );

?>