<?php
function sy_shortcodes_init() {
    function sy_cards($atts, $content = null) {
        return '<div class="sy_card_grid">' . do_shortcode($content) . '</div>';
    }

    function sy_search($atts) {
        ?>
        <script type="text/javascript">            
            jQuery(document).ready(function($) {

                <?php echo file_get_contents("autocomplete.js", __FILE__); ?>

                $.get("/wp-json/scrapyard/v1/types", function(res) {
                    autocomplete($("input.scrapyard-search")[0], res)
                })
                $("input.scrapyard-search").on("input propertychange", function() {
                    var search = $(this).val().toLowerCase()
                    $("div.sy_card").each(function() {
                        var found = false;
                        if ($(this).attr("name").toLowerCase().indexOf(search) >= 0) {
                            found = true
                        } else if ($(this).attr("description").toLowerCase().indexOf(search) >= 0) {
                            found = true
                        } else if ($(this).attr("type").toLowerCase().indexOf(search) >= 0) {
                            found = true
                        }
                        if(found) {
                            $(this).show()
                        } else {
                            $(this).hide();
                        }
                    })
                })
            });
        </script>
        <?php
        $out = '<div class="sy_search">';
        $out .= '<input type="text" class="scrapyard-search" placeholder="Search Scrapyard">';
        $out .= '</div>';
        return $out;
    }

    function sy_card($atts) {
        $atts = shortcode_atts(
            [
            'id' => '',
            ], $atts
        );

        $out = "";

        if(!isset($atts["id"]) || $atts["id"] == "") {
            return "";
        }


        $bot = scrapyard_get_item($atts['id']);
        if (count($bot->images) == 0) {
            $img_url = wp_get_attachment_url(0);
        } else {
            $img_url = wp_get_attachment_url( $bot->images[0]->image_id );
        }

        $type = "";
        foreach($bot->attributes as $struct) {
            if ("type" == strtolower($struct->name)) {
                $type = $struct->value;
                break;
            }
        }

        $out .= '
        <a href="/scrapyard-bot/?bot_id='. $atts['id'] .'">
        <div class="sy_card" name="'.str_replace('"', '', $bot->name).'" type="'.str_replace('"', '', $type).'" description="'.str_replace('"', '', $bot->description).'">
            <div class="sy_image" style="background-image: url(' . $img_url . ')">
            </div>
            <span class="sy_title">
            ' . $bot->name . '
            </span>
        </div></a>';
        return $out;
    }
    add_shortcode('sy_card', 'sy_card');
    add_shortcode('sy_cards', 'sy_cards');
    add_shortcode('sy_search', 'sy_search');

}
add_action('init', 'sy_shortcodes_init');
?>