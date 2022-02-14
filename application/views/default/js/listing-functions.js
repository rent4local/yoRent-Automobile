$(document).ready(function() {
    $(document).on('click', '#accordian li span.acc-trigger', function(e) {
        var link = $(this);
        var closest_ul = link.siblings("ul");
        console.log(closest_ul);
        /* // if (link.hasClass("is--active")) {
        //     closest_ul.slideUp();
        //     link.removeClass("is--active");
        // } else {
        //     closest_ul.slideDown();
        //     link.addClass("is--active");
        // } */
    });


    $('.productFilters-js').click(function(e) {
        /* //  e.stopPropagation(); */
    });


});