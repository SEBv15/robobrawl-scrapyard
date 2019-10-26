jQuery(function() {
    if(!readCookie("scrapyard-session")) {
        createCookie("scrapyard-session", generateUUID());
    }
    jQuery.get("/wp-json/scrapyard/v1/editing/"+readCookie("scrapyard-session"), function(res) {
        if(res == "ok") {
            jQuery("div.sya-block").hide();
        } else {
            jQuery("div.sya-block").append('<div class="sya-blockuser">Someone is already editing the scrapyard.</div>')
        }
    })
    function pingServer() {
        jQuery.get("/wp-json/scrapyard/v1/editing/"+readCookie("scrapyard-session"));
    }
    setInterval(pingServer, 3000);

    var $ = jQuery;

    $("div.sya_bot").each(function(index) {
        $(this).find("span.sya_title, img").click(function() {
            console.log("click");
            window.location = "?page=scrapyard&sy_item_id="+$(this).parent().attr("item_id");
        })
    })
})



jQuery(function() {
    var $ = jQuery;
    $("div.sya-add-attribute button").click(function(e) {
        if($("div.sya-add-attribute input.add-attribute-name").val()) {
            e.preventDefault();
            $.post("", {
                sya_action:"add-attribute", 
                sya_add_attr_name: $("div.sya-add-attribute input.add-attribute-name").val(),
                sya_add_attr_value: $("div.sya-add-attribute input.add-attribute-value").val()
            }, function() {
                window.location.reload();
            })
        }
    })
    $("form#sya-form div.sya-attribute button").click(function(e) {
        e.preventDefault();
        var name = $(this).parent().find("span").text();
        $.post("", {
            sya_action: "del-attribute",
            sya_del_attr_name: name
        }, function() {
            window.location.reload();
        })
    })
    $("form#sya-form div.sya-buttons button").click(function(e) {
        e.preventDefault();
        if(confirm("Delete?")) {
            $.post("", {
                sya_action: "delete"
            }, function() {
                window.location.replace("?page=scrapyard")
            })
        }
    })
    $.get("/wp-json/scrapyard/v1/types", function(res) {
        autocomplete($("[name=sy_attr-type]")[0], res)
    })
})











//////// FUNCTIONS

function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}
function generateUUID() { // Public Domain/MIT
    var d = new Date().getTime();//Timestamp
    var d2 = (performance && performance.now && (performance.now()*1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16;//random number between 0 and 16
        if(d > 0){//Use timestamp until depleted
            r = (d + r)%16 | 0;
            d = Math.floor(d/16);
        } else {//Use microseconds since page-load if supported
            r = (d2 + r)%16 | 0;
            d2 = Math.floor(d2/16);
        }
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}