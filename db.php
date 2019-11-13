<?php
/**
 * Creates new scrapyard item/bot.
 * 
 * @param String $name The name of the new bot
 * @param String $description Description of the new bot
 * @return Integer MYSQL id of the bot (pretty much useless)
 */
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
/**
 * Delete item/bot.
 * 
 * @param Integer $item_id Id of the bot to delete
 */
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

/**
 * Edit item/bot.
 * 
 * @param Integer $item_id The id of the bot to edit
 * @param String $name New name (null if not changed)
 * @param String $description New description (null if unchanged)
 * @param String $extra_html The extra html (null if unchanged)
 */
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

/**
 * Insert or Edit bot attribute.
 * 
 * @param Integer $item_id The id of the bot the attribute belongs to
 * @param String $name The name of the attribute
 * @param String $value The (new) value of the attribute
 * @return Integer|Null If new attribute, the MYSQL id of the attribute
 */
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
        return $wpdb->insert_id;
    } else {
        $wpdb->update($botattributes_table,array(
            'value' => $value
        ), array(
            'name' => $name,
            'item_id' => $item_id
        ));
    }
}

/**
 * Delete bot attribute.
 * 
 * @param Integer $item_id The id of the bot the attribute belongs to
 * @param String $name The name of the attribute to delete
 */
function scrapyard_delete_attr($item_id, $name) {
    global $wpdb;
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    $wpdb->query("DELETE FROM $botattributes_table WHERE (item_id=$item_id AND name='$name')");
}

/**
 * Get all the types user's have given their bot so far (Vertical Spinner, Horizontal Spinner, etc).
 * 
 * @return Array Array of bot types
 */
function scrapyard_get_types() {
    global $wpdb;
    $botattributes_table = $wpdb->prefix . 'scrapyard_botattributes';

    function objToStr($v) {
        return $v->value;
    }

    $types = $wpdb->get_results("SELECT DISTINCT value FROM $botattributes_table WHERE (name='type') ORDER BY value ASC");
    return array_map("objToStr", $types);
}

/**
 * Insert new image for bot.
 * 
 * @param Integer $item_id The id of the bot the attribute belongs to
 * @param Integer $image_id The wordpress gallery image id of the image
 * @return Integer The MYSQL id of the image
 */
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

/**
 * Delete image from bot.
 * 
 * @param Integer $id The MYSQL id of the image (I don't know why I did it this way but it's working so far)
 */
function scrapyard_delete_img($id) {
    global $wpdb;
    $botimages_table = $wpdb->prefix . 'scrapyard_botimages';
    
    $wpdb->delete($botimages_table, array(
        'id' => $id
    ));
}

/**
 * Reorder Images.
 * 
 * @param Integer $item_id The id of the bot the attribute belongs to
 * @param Integer $from The previous index of the image
 * @param Integer $to The new index of the image relative to the old order
 */
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

/**
 * Get Bot/item.
 * 
 * @param Integer $item_id The id of the bot the attribute belongs to
 * @return Array The All the bots attributes with the images at ->images and attributes at ->attributes
 */
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

/**
 * Get the item_id for all bots in the database.
 * 
 * @return Array the item_ids of all bots
 */
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
?>
