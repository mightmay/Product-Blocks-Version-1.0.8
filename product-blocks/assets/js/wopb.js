(function($) {
    'use strict';

    // *************************************
    // Flex Menu
    // *************************************
    $(document).ready(function(){
        $('ul.wopb-flex-menu').flexMenu();
    });
       

    // *************************************
    // Loadmore Append
    // *************************************
    $('.wopb-loadmore-action').on('click', function(e){
        e.preventDefault();

        let that    = $(this),
            parents = that.closest('.wopb-block-wrapper'),
            paged   = parseInt(that.data('pagenum')),
            pages   = parseInt(that.data('pages'));
        
        if(that.hasClass('wopb-disable')){
            return
        }else{
            paged++;
            that.data('pagenum', paged);
            if(paged == pages){
                $(this).addClass('wopb-disable');
            }else{
                $(this).removeClass('wopb-disable');
            }
        }

        $.ajax({
            url: wopb_data.ajax,
            type: 'POST',
            data: {
                action: 'wopb_load_more', 
                paged: paged ,
                blockId: that.data('blockid'),
                postId: that.data('postid'),
                blockName: that.data('blockname'),
                wpnonce: wopb_data.security
            },
            beforeSend: function() {
                parents.addClass('wopb-loading-active');
            },
            success: function(data) {
                $(data).insertBefore( parents.find('.wopb-loadmore-insert-before') );
            },
            complete:function() {
                parents.removeClass('wopb-loading-active');
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                parents.removeClass('wopb-loading-active');
            },
        });
    });


    // *************************************
    // Filter
    // *************************************
    $('.wopb-filter-wrap li a').on('click', function(e){
        e.preventDefault();

        if($(this).closest('li').hasClass('filter-item')){
            let that    = $(this),
                parents = that.closest('.wopb-filter-wrap'),
                wrap = that.closest('.wopb-block-wrapper');

                parents.find('a').removeClass('filter-active');
                that.addClass('filter-active');

            $.ajax({
                url: wopb_data.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_filter', 
                    taxtype: parents.data('taxtype'),
                    taxonomy: that.data('taxonomy'),
                    blockId: parents.data('blockid'),
                    postId: parents.data('postid'),
                    blockName: parents.data('blockname'),
                    wpnonce: wopb_data.security
                },
                beforeSend: function() {
                    wrap.addClass('wopb-loading-active');
                },
                success: function(data) {
                    wrap.find('.wopb-block-items-wrap').html(data);
                },
                complete:function() {
                    wrap.removeClass('wopb-loading-active');
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                    wrap.removeClass('wopb-loading-active');
                },
            });
        }
    });


    // *************************************
    // Pagination Number
    // *************************************
    function showHide(parents, pageNum, pages) {
        if (pageNum == 1) {
            parents.find('.wopb-prev-page-numbers').hide()
            parents.find('.wopb-next-page-numbers').show()
        } else if (pageNum == pages){
            parents.find('.wopb-prev-page-numbers').show()
            parents.find('.wopb-next-page-numbers').hide()
        } else {
            parents.find('.wopb-prev-page-numbers').show()
            parents.find('.wopb-next-page-numbers').show()
        }


        if(pageNum > 2) {
            parents.find('.wopb-first-pages').show()
            parents.find('.wopb-first-dot').show()
        }else{
            parents.find('.wopb-first-pages').hide()
            parents.find('.wopb-first-dot').hide()
        }
        
        if(pages > pageNum + 1){
            parents.find('.wopb-last-pages').show()
            parents.find('.wopb-last-dot').show()
        }else{
            parents.find('.wopb-last-pages').hide()
            parents.find('.wopb-last-dot').hide()
        }
    }

    function serial(parents, pageNum, pages){
        let datas = pageNum < 2 ? [1,2,3] : ( pages == pageNum ? [pages-2,pages-1, pages] : [pageNum-1,pageNum,pageNum+1] )
        let i = 0
        parents.find('.wopb-center-item').each(function() {
            if(pageNum == datas[i]){
                $(this).addClass('pagination-active')
            }
            $(this).attr('data-current', datas[i]).find('a').text(datas[i])
            i++
        });
    }

    $('.wopb-pagination-ajax-action li').on('click', function(e){
        e.preventDefault();

        let that    = $(this),
            parents = that.closest('.wopb-pagination-ajax-action'),
            wrap = that.closest('.wopb-block-wrapper');

        let pageNum = 1;
        let pages = parents.attr('data-pages');
        
        if( that.data('current') ){
            pageNum = Number(that.attr('data-current'))
            parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
            serial(parents, pageNum, pages)
            showHide(parents, pageNum, pages)
        } else {
            if (that.hasClass('wopb-prev-page-numbers')) {
                pageNum = Number(parents.attr('data-paged')) - 1
                parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
                parents.find('li[data-current="'+pageNum+'"]').addClass('pagination-active')
                serial(parents, pageNum, pages)
                showHide(parents, pageNum, pages)
            } else if (that.hasClass('wopb-next-page-numbers')) {
                pageNum = Number(parents.attr('data-paged')) + 1
                parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
                parents.find('li[data-current="'+pageNum+'"]').addClass('pagination-active')
                serial(parents, pageNum, pages)
                showHide(parents, pageNum, pages)
            }
        }

        if(pageNum){
            $.ajax({
                url: wopb_data.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_pagination', 
                    paged: pageNum,
                    blockId: parents.data('blockid'),
                    postId: parents.data('postid'),
                    blockName: parents.data('blockname'),
                    wpnonce: wopb_data.security
                },
                beforeSend: function() {
                    wrap.addClass('wopb-loading-active');
                },
                success: function(data) {
                    wrap.find('.wopb-block-items-wrap').html(data);
                },
                complete:function() {
                    wrap.removeClass('wopb-loading-active');
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                    wrap.removeClass('wopb-loading-active');
                },
            });
        }
    });
    
    // *************************************
    // SlideShow
    // *************************************
    $('.wopb-product-blocks-slide').each(function () {
        const that = $(this)
        const slideBrealpoint = that.data('slidestoshow').split('-');
        that.slick({
            arrows:         that.data('showarrows') ? true : false,
            dots:           that.data('showdots') ? true : false,
            infinite:       true,
            speed:          500,
            slidesToShow:   parseInt(slideBrealpoint[0]),
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1199,
                    settings: {
                        slidesToShow: parseInt(slideBrealpoint[1]),
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: parseInt(slideBrealpoint[2]),
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: parseInt(slideBrealpoint[3]),
                        slidesToScroll: 1
                    }
                }
            ],
            autoplay:       that.data('autoplay') ? true : false,
            autoplaySpeed:  that.data('slidespeed') || 3000,
            cssEase:        "linear",
            prevArrow:      that.parent().find('.wopb-slick-prev').html(),
            nextArrow:      that.parent().find('.wopb-slick-next').html(),
        });
    });


    
    // *************************************
    // Quick View
    // *************************************
    $('.wopb-quick-view').on('click', function(e){
        e.preventDefault();
        const _modal = $('.wopb-modal-wrap')
        const _postId = $(this).data('postid')
        if(_postId){
            $.ajax({
                url: wopb_data.ajax,
                type: 'POST',
                data: { 
                    action: 'wopb_quick_view', 
                    postid: _postId,
                    wpnonce: wopb_data.security
                },
                beforeSend: function() {
                    _modal.addClass('active');
                    _modal.find('.wopb-modal-loading').addClass('active');
                },
                success: function(data) {
                    _modal.find('.wopb-modal-body').html(data);
                },
                complete:function() {
                    _modal.find('.wopb-modal-loading').removeClass('active');
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                    //_modal.removeClass('wopb-loading-active');
                },
            });
        }
    });

    $('.wopb-modal-close').on('click', function(e){
        e.preventDefault();
        $(this).closest('.wopb-modal-wrap').removeClass('active');
    });
    

    // *************************************
    // Quick Add to Cart
    // *************************************
    $(document).on('change', '.wopb-add-to-cart-quantity', function(e){
        e.preventDefault();
        $(this).closest('.wopb-add-to-cart').find('.add_to_cart_button').attr('data-quantity', $(this).val());
    });
    $(document).on('click', '.wopb-add-to-cart-plus', function(e){
        e.preventDefault();
        const parents = $(this).closest('.wopb-add-to-cart')
        const quantity = parseInt(parents.find('.wopb-add-to-cart-quantity').val())
        parents.find('.add_to_cart_button').attr('data-quantity', quantity + 1 );
        parents.find('.wopb-add-to-cart-quantity').val( quantity + 1 );
    });
    $(document).on('click', '.wopb-add-to-cart-minus', function(e){
        e.preventDefault();
        const parents = $(this).closest('.wopb-add-to-cart')
        const quantity = parseInt(parents.find('.wopb-add-to-cart-quantity').val())
        if(quantity >= 2){
            parents.find('.add_to_cart_button').attr('data-quantity', quantity - 1 );
            parents.find('.wopb-add-to-cart-quantity').val( quantity - 1 );
        }
    });

        
})( jQuery );