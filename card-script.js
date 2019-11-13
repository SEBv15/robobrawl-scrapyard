jQuery(function($) {
    $(window).load(resizeGrid)
    $(window).resize(resizeGrid)

    function resizeGrid() {
        var elems = this.document.getElementsByClassName("sy_card_grid")
        for (let i = 0; i <elems.length; i++) {
            this.console.log(elems[i])
            var w = elems[i].parentElement.offsetWidth;
            w = w - (w % (256 + 16*2));
            elems[i].style.width = w + "px";
        }
    }
})

