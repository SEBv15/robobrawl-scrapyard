<?php
/**
 * Plugin Name: Scrapyard
 * Description: Dead Robots!
 * Version: 1.0
 * Author: Sebastian Strempfer
 */

// Create Database table if they don't exist yet on plugin activation
include_once("install_db.php");

include_once("shortcodes.php");
include_once("admin/sy_admin.php");
include_once("bot-page/register-bot-page.php");

/**
 * Register style and script for card shortcodes.
 */
function sy_load_plugin_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'sy', $plugin_url . 'card-style.css' );
    wp_enqueue_script('sy', $plugin_url . 'card-script.js', array('jquery'));
}
add_action( 'wp_enqueue_scripts', 'sy_load_plugin_scripts' );

include_once('db.php');

?>