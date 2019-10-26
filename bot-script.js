jQuery(function() {
    var $ = jQuery;
    console.log("RUNNING")
    $(".syb-gallery").slick({
        dots: true,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
      });
})