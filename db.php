<?php

function scrapyard_add_item($name, $description = "") {
    global $wpdb;

	$bots_table = $wpdb->prefix . 'scrapyard_bots';
    
    $item_id = get_option("scrapyard_item_counter");

    update_option("scrapyard_item_counter", $item_id + 1);

    $wpdb->insert($bots_table, array(
        'name' => $name,
        'description' => $description,
        'item_id' => $item_id
    ));

    return $wpdb->insert_id;
}
function scrapyard_delete_item($item_id) {
    global $wpdb;

	$bots_table = $wpdb->prefix . 'scrapyard_bots';
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    $wpdb->delete($bots_table, array(
        'item_id' => $item_id
    ));
    $wpdb->delete($botimages_table, array(
        'item_id' => $item_id
    ));
    $wpdb->delete($botattributes_table, array(
        'item_id' => $item_id
    ));
}

// submit null when not changed
function scrapyard_edit_item($item_id, $name=null, $description=null, $extra_html=null) {
    global $wpdb;

    $bots_table = $wpdb->prefix . 'scrapyard_bots';

    $update = array();

    if($name != null) {
        $update['name'] = $name;
    }
    if($description != null || $description === "") {
        $update['description'] = $description;
    }
    if($extra_html != null || $extra_html === "") {
        $update['extra_html'] = $extra_html;
    }
    
    $wpdb->update($bots_table, $update, array(
        'item_id' => $item_id
    ));
}

function scrapyard_upsert_attr($item_id, $name, $value) {
    global $wpdb;
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    $res = $wpdb->get_results("SELECT * FROM $botattributes_table WHERE (item_id=$item_id AND name='$name')");

    if($res == null) {
        $wpdb->insert($botattributes_table, array(
            'name' => $name,
            'value' => $value,
            'item_id' => $item_id
        ));
    } else {
        $wpdb->update($botattributes_table,array(
            'value' => $value
        ), array(
            'name' => $name,
            'item_id' => $item_id
        ));
    }

    return $wpdb->insert_id;
}

function scrapyard_delete_attr($item_id, $name) {
    global $wpdb;
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    $wpdb->query("DELETE FROM $botattributes_table WHERE (item_id=$item_id AND name='$name')");
}

function scrapyard_get_types() {
    global $wpdb;
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    function objToStr($v) {
        return $v->value;
    }

    $types = $wpdb->get_results("SELECT DISTINCT value FROM $botattributes_table WHERE (name='type') ORDER BY value ASC");
    return array_map("objToStr", $types);
}

function scrapyard_insert_img($item_id, $image_id) {
    global $wpdb;
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';

    $max_id = $wpdb->get_var("SELECT max(id) FROM $botimages_table");
    $pos = 1;
    if($max_id != null) {
        $pos = $max_id + 1;
    }

    $wpdb->insert($botimages_table, array(
        'pos' => $pos,
        'image_id' => $image_id,
        'item_id' => $item_id,
    ));

    return $wpdb->insert_id;
}
function scrapyard_delete_img($id) {
    global $wpdb;
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';
    
    $wpdb->delete($botimages_table, array(
        'id' => $id
    ));
}
function scrapyard_reorder_img($item_id, $from, $to) {
    // ATTENTION!!!!!
    // $to is relative to the old table
    global $wpdb;
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';
    
    $res = $wpdb->get_results("SELECT * FROM $botimages_table WHERE (item_id=$item_id) ORDER BY pos ASC");

    $from_id = $res[$from]->id;

    if($to > 0) {
        $before_pos = $res[$to-1]->pos;
    } else {
        $before_pos = 0;
    }
    if($to >= count($res)) {
        $after_pos = $res[count($res) - 1]->pos + 2;
    } else {
        $after_pos = $res[$to]->pos;
    }

    $new_pos = ($before_pos + $after_pos) / 2;

    $wpdb->update($botimages_table, array(
        'pos' => $new_pos
    ), array(
        'id' => $from_id
    ));
}

function scrapyard_get_item($item_id) {
    global $wpdb;
	$bots_table = $wpdb->prefix . 'scrapyard_bots';
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    $bot = $wpdb->get_results("SELECT * FROM $bots_table WHERE item_id=$item_id");
    if(count($bot) == 0 || $bot == null) {
        return array();
    }
    $bot = $bot[0];
    $bot->images = $wpdb->get_results("SELECT id, image_id, pos, item_id FROM $botimages_table WHERE item_id=$item_id ORDER BY pos ASC");
    $bot->attributes = $wpdb->get_results("SELECT id, name, value FROM $botattributes_table WHERE item_id=$item_id ORDER BY name ASC");

    return $bot;
}
function scrapyard_get_item_ids() {
    global $wpdb;
    $bots_table = $wpdb->prefix . 'scrapyard_bots';
    $res = $wpdb->get_results("SELECT item_id FROM $bots_table");
    $out = array();
    foreach($res as $key => $val) {
        array_push($out, $val->item_id);
    }
    return $out;
}

global $scrapyard_db_version;
$scrapyard_db_version = '1.0';

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
    add_option('scrapyard_item_counter', 0);
}
?>
