/*
 * menu
 */
$(function(){
    var
        isHidden = true,
        nav = $('.navbar-list'),
        search = $('.navbar-search');

    $('#touch-menu-btn').on('click', function(){
        if(isHidden){
            nav.show();
            search.show();
        }else{
            nav.hide();
            search.hide();
        }
        isHidden = !isHidden;
    });
});


/*
 * Search
 */
$(function(){
    var
        form = $('#search-form'),
        text = form.find('input:text'),
        btn = $('.search-form-submit'),
        searchURL = 'http://www.bing.com/search?q=',
        keyword;

    form.attr('action', '');
    text.attr('placeholder', '请输入关键字');

    btn.on('click', function(){
        keyword = text.val();

        if(keyword.replace(' ', '') == '')
            return false;

        //form.attr('action', searchURL + keyword);
        //$('#search-form').submit();
        location.href = searchURL + keyword;
    });
});

/* footer */
$(function(){
    var
        windowHeight = $(window).height(),
        headerHeight = $('.header').outerHeight(true),
        footerHeight = $('.footer').outerHeight(true);

    $('.wrapper > .content').css({'min-height' : windowHeight - headerHeight - footerHeight});
});

/* Redirect to next page */
$(function(){
    $('.page-jump input').each(function(){
        var
            _this = $(this);

        if(_this.attr('data-redirect'))
            _this.on('click', function(){
                location.href = $(this).attr('data-redirect');
            });
    });

});