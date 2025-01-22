

$(document).ready(function () {


    $('#header').removeClass('shrink');
    $('#logo img').css('max-height', '100px');
    $('.content').css('padding-top', '100px');
});

$(window).scroll(function () {
    if ($(this).scrollTop() > 50) {
        $('#header').addClass('shrink');
        $('#logo img').css('max-height', '50px');
        $('.content').css('padding-top', '50px');
    } else {
        $('#header').removeClass('shrink');
        $('#logo img').css('max-height', '100px');
        $('.content').css('padding-top', '100px');
    }

});


