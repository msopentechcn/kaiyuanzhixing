
/*
 * Slider
 */
$(function() {
    $('.slider.information').unslider({
        speed      : 500,            //  The speed to animate each slide (in milliseconds)
        delay      : 6000,           //  The delay between slide animations (in milliseconds)
        loop       : true,
        //complete : function() {},  //  A function that gets called after every slide animation
        //keys     : true,           //  Enable keyboard (left, right) arrow shortcuts
        dots       : true,           //  Display dot navigation
        fluid      : false,          //  Support responsive design. May break non-responsive designs
        arrows     : true,
        autoplay: true
    });

    var
        initSlideBanner = (function(){
            var
                banner,
                config = {
                    delay: 4000,
                    loop: true,
                    autoplay: false
                },
                slideContainer = '.slider.banner',
                arrowKey = '.unslider-arrow';

            var
                init = function(){
                    //banner = $(slideContainer).unslider(config).data('unslider');
                    banner = $(slideContainer).unslider(config);

                    $(arrowKey).click(function() {
                        var fn = this.className.split(' ')[3];

                        //  Either do unslider.data('unslider').next() or .prev() depending on the className
                        banner.data('unslider')[fn]();
                    });
                };

            var
                destroy = function(){
                    if(banner && banner.stop)
                        banner.stop();
                };

            return {
                init : init,
                destroy : destroy
            }
        })();

    if(window.matchMedia && window.matchMedia('(min-width:961px)').matches){
        initSlideBanner.init();
    }

});



/* member logos fadeIn and fadeOut animate */
$(function(){
    var
        container = $('#member-logos-fadeInOut'),
        ps = container.find('p'),
        divs = container.find('div'),
        currP,
        currDiv;

    ps.each(function(){
        $(this).hide();
    });

    divs.each(function(){
        $(this).hide();
    });


    var
        cache = [],
        node;

    container.children().each(function(){
        var
            _this = $(this);

        if(_this.prop('tagName') == 'P'){
            if(node)
                cache.push(node);
            node = {};
            node.p = _this;
        }else{
            node.divs = node.divs || [];
            node.divs.push(_this);
        }
    });

    cache.push(node);
    container.show();

    var
        animateSpeed = 500,
        animating = function(){
            var
                _currP,
                _currDiv,
                _needChangeP = false;

            if(typeof currDiv == 'undefined'){
                currP = 0;
                currDiv = 0;

                cache[currP].p.fadeIn(animateSpeed);
                cache[currP].divs[currDiv].fadeIn(animateSpeed);
            }else{
                _currP = currP;
                _currDiv = currDiv;

                if(currDiv >= cache[currP].divs.length - 1){
                    _needChangeP = true;
                    if(currP >= cache.length - 1){
                        currP = 0;
                    }else{
                        currP += 1;
                    }
                    currDiv = 0;
                }else{
                    currDiv += 1;
                }

                if(_needChangeP)
                    cache[_currP].p.fadeOut(animateSpeed, function(){
                        cache[currP].p.fadeIn();
                    });

                cache[_currP].divs[_currDiv].fadeOut(animateSpeed, function(){
                    cache[currP].divs[currDiv].fadeIn();
                });
            }
        };

    var
        step = 6000,
        timer,
        timed = function(){
            animating();
            timer = setTimeout(timed, step);
        };

    timed();
});
