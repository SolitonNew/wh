@if(\App\Http\Models\PlanVideoModel::count())
<div class="video-list-view">
    <div class="alert alert-light video-list-title">@lang('terminal.video_title')</div>
    <div class="video-list">
        @foreach(\App\Http\Models\PlanVideoModel::orderBy('order_num', 'asc')->get() as $row)        
        <a class="video-list-item" href="#">
            <video class="video-list-item-content" autoplay="true" poster="img/cams/{{ $row->name }}.png"></video>
        </a>
        @endforeach
    </div>
</div>

<script>
    var videoViewW = 0;
    var videoItemW = 0;
    var videoItemW_2 = 0;
    var videoTimeOutForScroll = false;
    var videoLastScroll = false;
    
    $('document').ready(() => {
        $('.video-list').on('scroll', (e) => {
            videoViewRecalc();
        });
       
        $(window).on('resize', (e) => {
            videoViewW = $('.video-list-view').width();
            videoItemW = $('.video-list-item').width() + 20;
            videoItemW_2 = videoItemW / 2;
            videoViewRecalc();
            
            let w = $('.video-list .video-list-item').length * videoItemW;
            if (w < videoViewW) {
                $('.video-list-title').css({
                    width: 'calc(' + (w + 20) + 'px + 6rem)',
                });
            } else {
                $('.video-list-title').width('100%');
            }
        }).trigger('resize');
        
        $('.video-list').on('touchstart', (e) => {
            $('.body-page-main').css('overflow-x', 'hidden');
            clearTimeout(videoTimeOutForScroll);
        });
        
        $('.video-list').on('touchend', (e) => {
            $('.body-page-main').css('overflow-x', 'auto');
            videoViewCheckAutoscroll();
        });
        
        $('.video-list-item').on('click', (e) => {
            e.preventDefault();
        });
    });
   
    function videoViewRecalc() {      
        let scrollX = $('.video-list').scrollLeft();
        let p_off = $('.video-list').offset().left;
        let cx = (videoViewW - videoItemW) / 2;
        let ls = $('.video-list-item');
       
        if (videoViewW >= 992) {
            $(ls).css('opacity', 1);
        } else {
            for (let i = 0; i < ls.length; i++) {
                let itemX = $(ls[i]).offset().left - p_off - cx;
                let o = 1 - Math.abs(itemX / videoItemW / 1.25);
                if (o < 0) {
                    o = 0;
                }
                $(ls[i]).css('opacity', o);
            }
        }
    }
    
    function videoViewCentringItem() {
        let scrollX = $('.video-list').scrollLeft();
        let p_off = $('.video-list').offset().left;
        let cx = (videoViewW - videoItemW) / 2;
        let ls = $('.video-list-item');
        var prevX = 0;
        var prevO = 0;
       
        if (videoViewW >= 992) {
            //
        } else {
            for (let i = 0; i < ls.length; i++) {
                let itemX = $(ls[i]).offset().left - p_off - cx;
                let o = 1 - Math.abs(itemX / videoItemW);
                if (o < 0) {
                    o = 0;
                }
                if (o > prevO) {
                    prevX = itemX + cx;
                    prevO = o;
                }
            }
            
            if (prevO > 0) {
                let s = scrollX + prevX - cx + 0.5;
                $('.video-list').stop().animate({scrollLeft: s}, 150);
            }
        }
    }
    
    function videoViewCheckAutoscroll() {
        videoTimeOutForScroll = setTimeout(() => {
            let s = $('.video-list').scrollLeft();
            if (s == videoLastScroll) {
                videoViewCentringItem();
            } else {
                videoLastScroll = s;
                videoViewCheckAutoscroll();
            }
        }, 100);
    }
   
</script>
@endif