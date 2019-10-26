<?php
global $scrapyard_db_version;
$scrapyard_db_version = '1.0';

register_activation_hook( __FILE__, 'scrapyard_install' );

/**
 * Create database tables for the plugin if they don't exist yet.
 */
function scrapyard_install() {
    global $wpdb;
    global $scrapyard_db_version;

	$bots_table_name = $wpdb->prefix . 'scrapyard_bots';
	$botimages_table_name = $wpdb->prefix . 'scrapyard_botimages';
	$botattributes_table_name = $wpdb->prefix . 'scrapyard_botattributes';
	
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "CREATE TABLE IF NOT EXISTS $bots_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name text NOT NULL,
		description text NOT NULL,
        item_id bigint(9) NOT NULL,
        extra_html text,
		PRIMARY KEY  (id)
	) $charset_collate;";

    dbDelta( $sql );
    
	$sql = "CREATE TABLE IF NOT EXISTS $botimages_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
        image_id bigint(9) NOT NULL,
        pos float(53) NOT NULL,
        item_id bigint(9) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

    dbDelta( $sql );
    
	$sql = "CREATE TABLE IF NOT EXISTS $botattributes_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
        name text NOT NULL,
        value text NOT NULL,
        item_id bigint(9) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

    dbDelta( $sql );
    
    add_option( 'scrapyard_db_version', $scrapyard_db_version );
    // Counter to for new item_id
    if (get_option('scrapyard_item_counter') == null) {
        add_option('scrapyard_item_counter', 0);
    }
}
?>