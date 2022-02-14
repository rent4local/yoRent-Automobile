
$(document).ready(function(){
    var _carousel = $('.js-carousel');
    _carousel.each(function() {
        var _this = $(this),
            _slidesToShow = (_this.data("slides")).toString().split(',');
        _this.slick({
            slidesToShow: parseInt(_slidesToShow.length > 0 ? _slidesToShow[0] : "3"),
            slidesToScroll: 3,
            centerMode: _this.data("mode"),
            arrows: _this.data("arrows"),
            vertical: _this.data("vertical"),
            dots: _this.data("slickdots"),
            infinite: _this.data("infinite"),
            autoplay: false,
            pauseOnHover: false,
            centerPadding: 0,
            adaptiveHeight: true,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 1 ? _slidesToShow[1] : "2")),
                        slidesToScroll: 2,
                        vertical: false
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 2 ? _slidesToShow[2] : "1")),
                        vertical: false
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 3 ? _slidesToShow[3] : "1")),
                        slidesToScroll: 1,
                        vertical: false
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 4 ? _slidesToShow[4] : "1")),
                        slidesToScroll: 1,
                        vertical: false
                    }
                }
            ]
        });
    });


/*Common toggle*/
var _body = $('body');
var _toggle = $('[data-tgl]');
_toggle.each(function() {
    var _visible = 'visible'; 
    var _open = 'open'; 

    var _this = $(this),
        _thisHref = _this.attr('href'),
        _target = $(_thisHref.substr(_thisHref.indexOf('#'))),
        _close = _target.find('.js-close[href="' + _thisHref + '"]');
        var _isToggle = 'is-' + _this.data('tgl'); 

    if (_this.data('classes')) {
        _visible = _this.data('classes');
    }

    _close.each(function() {
        $(this).on('click', function(e) {
            e.preventDefault();

            _target.removeClass(_visible);
            _this.removeClass(_open);
            _body.removeClass(_isToggle);
            _body.attr('data-toggleid', '');
            
        });
    });

    _this.on('click', function(e) {
        /* [ HIDE PREVIOUS OPENED DROPDOWN  */
        $('.dropdown').removeClass('show');
        $('.dropdown-menu').removeClass('show');
        /* ] */
    
        var currentTargetId = _target.attr('id');
        if (_body.attr('data-toggleid') != undefined && _body.attr('data-toggleid') != '' && currentTargetId != _body.attr('data-toggleid')) {
            var previousElmt = $('#'+ _body.attr('data-toggleid'));
            $('a[href="#'+ _body.attr('data-toggleid') +'"]').removeClass(_open);
            var previousElmtBodyClss = $(_body.attr('data-togglecls'));
            previousElmt.removeClass(_visible);
            /* previousElmt.removeClass(_open); */
            _body.removeClass(previousElmtBodyClss);
        }

        $('html').css('overflow', '');
        e.preventDefault();
        e.stopPropagation();

        if (_this.hasClass(_open) && _target.hasClass(_visible) && _body.hasClass(_isToggle)) {
            _target.removeClass(_visible);
            _this.removeClass(_open);
            _body.removeClass(_isToggle);
            _body.attr('data-togglecls', '');
            _body.attr('data-toggleid', '');
        } else {
            if (!(`${_this.data('outside')}` === 'false')) {
                _toggle.each(function() {
                    var __this = $(this),
                        __thisHref = __this.attr('href'),
                        __target = $(__thisHref.substr(__thisHref.indexOf('#')));

                    if (!__this.parents('[class*="visible"]').length) {
                        __target.removeClass(_visible);
                        __this.removeClass(_open);
                    }
                });
            }

            if (_this.prop('rel')) {
                $(`[rel="${_this.prop('rel')}"]`).each(function() {

                    var __this = $(this),
                        __thisHref = __this.attr('href'),
                        __target = $(__thisHref.substr(__thisHref.indexOf('#')));
                    __target.removeClass(_visible);
                    __this.removeClass(_open);

                });
            }
            _target.addClass(_visible);
            _this.addClass(_open);
            _body.addClass(_isToggle); 
            _body.attr('data-toggleid', _target.attr('id'));
            _body.attr('data-toggleclss', _isToggle);
            setTimeout(function () {
                $(_thisHref).find('input:text:visible:first').focus();
            }, 500);
        }     

    });

    _target.on('click', function(e) {
        e.stopPropagation();
    });

    if (!(`${_this.data('outside')}` === 'false')) {
        $(document).on('click', function(event) {
            _target.removeClass(_visible);
            _this.removeClass(_open);
            _body.removeClass(_isToggle);
            _body.attr('data-toggleid', '');
            _body.attr('data-toggleclss', '');
        });
    }

});
/*End of Toggle*/
    $('.location_trigger').on('click', function(e){
        //e.stopPropagation();
        $('body').toggleClass('is-open-location');
    });
    $('.common-overlay').on('click', function(e){
        $('body').removeClass('is-open-location');
    });

    $('.dropdown-toggle').on('click', function(e) {
        var bodyToggleClass = $('body').data('toggleclss');
        if(bodyToggleClass != '' && bodyToggleClass != undefined) {
            var activeElmtId = $('body').data('toggleid');
            $('#'+activeElmtId).removeClass('visible');
            $('a[href="#'+ activeElmtId +'"]').removeClass('open');
            $('body').removeClass(bodyToggleClass);
            $('body').attr('data-togglecls', '');
            $('body').attr('data-toggleid', '');
        }
    });

});







