@extends('index')

@section('head')
<link rel="stylesheet" href="/css/terminal.css">
@endsection

@section('body')

    <div class="body-page-main">
        <div class="body-page-left">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <br>
            <div class="alert alert-primary">
                @lang('terminal.menu_back')
            </div>
        </div>
        <div class="body-page-center">
            <div id="dummyNav"></div>
            <div id="mainContainer" class="container-fluid" style="overflow: hidden;">
            @yield('content')
            </div>
        </div>
        <div class="body-page-right">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <br>
            <div class="alert alert-primary">
                @lang('terminal.menu_checked')
            </div>
        </div>
    </div>


<script>
    var currentPage = '';
    var isMobile = false;
    var lockScrollLeft = false;
    var lockScrollRight = false;
    
    $('document').ready(() => {
        isMobile = (window.orientation !== undefined);
        
        lockScrollLeft = {{ Request::url() == route('home') ? 'true' : 'false' }};
        lockScrollRight = {{ Request::segment(1) == 'checked' ? 'true' : 'false' }};
                
        if (!isMobile) {
            $('.body-page-main').css('overflow', 'hidden');
        }
        
        if (lockScrollLeft) {
            $('.body-page-left').addClass('body-page-lock');
        }
        
        if (lockScrollRight) {
            $('.body-page-right').addClass('body-page-lock');
        }
                
        $('.custom-control-input').on('change', (e) => {
            let obj = $(e.target);
            varID = obj.attr('id').substr(9);
            if (obj.prop('checked')) {
                varVal = 1;
            } else {
                varVal = 0;
            }
            $.ajax({
                method: "POST",
                url: "{{ route('terminal.variable-set', ['', '']) }}/" + varID + "/" + varVal,
                data: {_token: '{{ csrf_token() }}'},
            }).done((data)=>{
                if (data) {
                    alert(data);
                }
            });
        });
        
        loadChanges();
        
        $(window).on('resize', () => {
            $('.body-page-main').scrollLeft($('.body-page-left').width());
            
            if ($('nav').length) {
                $('body').addClass('fixed-nav');
                $('#dummyNav').height($('nav').height());
            }
        }).resize();
        
        $(window).scroll((e) => {
            if ($('nav').length) {               
                if ($(window).scrollTop() > 5) {
                    $('body').addClass('fixed-nav-offset');
                } else {
                    $('body').removeClass('fixed-nav-offset');
                }
            }
        }).scroll();
        
        $('.body-page-main > div').css('opacity', 1);
    });
    
    let lastVariableID = {{ App\Http\Models\VariableChangesMemModel::lastVariableID() }};
    
    function loadChanges() {
        $.ajax({url: '{{ route("terminal.variable-changes", ['']) }}/' + lastVariableID, 
        success: (data) => {           
            setTimeout(loadChanges, 500);
            
            if ((typeof(data) == 'string') && (data.substr(0, 9) == 'LAST_ID: ')) {
                lastVariableID = data.substr(9);
                console.log('LAST_ID = ' + lastVariableID);
            } else {
                for (let i = 0; i < data.length; i++) {
                    let rec = data[i];
                    let varID = parseInt(rec.VARIABLE_ID);
                    let varValue = parseFloat(rec.VALUE);
                    let varTime = parseInt(rec.CHANGE_DATE);
                    lastVariableID = rec.ID;
                    
                    /* Call Event */
                    variableOnChanged(varID, varValue, varTime);
                    /* ---------- */
                }
            }
        }, 
        error: () => {
            setTimeout(loadChanges, 5000);
            console.log('ERROR');
        }});
    }

    var bodyItemW = 0;
    var bodyItemW_2 = 0;
    var bodyTimeOutForScroll = false;
    var bodyLastScroll = false;
    
    $('document').ready(() => {                
        $('.body-page-main').scroll((e) => {
            let sl = $('.body-page-main').scrollLeft();
            
            if (sl < bodyItemW && lockScrollLeft) {
                $('.body-page-left').css('opacity', 0.5 - (sl / bodyItemW) / 2);
            } else
            if (sl > bodyItemW && lockScrollRight) {
                $('.body-page-right').css('opacity', ((sl - bodyItemW) / bodyItemW) / 2);
            }
            
            let itemX = sl - bodyItemW;
            let o = 1 - Math.abs(itemX / bodyItemW);
            $('nav').css('opacity', o);
            recalcSpinerPos();
        });
       
        $(window).on('resize', (e) => {
            bodyItemW = $('.body-page-center').width();
            bodyItemW_2 = bodyItemW / 2;           
            recalcSpinerPos();
        }).trigger('resize');
        
        $('.body-page-main').on('touchend', () => {
            bodyViewCheckAutoscroll();
        });
        
        $('.body-page-main').on('touchstart', () => {
            clearTimeout(bodyTimeOutForScroll);
        });
    });
    
    function bodyViewCentringItem() {        
        let scrollX = $('.body-page-main').scrollLeft();
        let ls = $('.body-page-main > div');
        var prevX = 0;
        var prevO = 0;
       
        if (bodyItemW >= 992) {
            //
        } else {
            for (let i = 0; i < ls.length; i++) {
                let itemX = $(ls[i]).offset().left;
                let o = 1 - Math.abs(itemX / bodyItemW);
                if (o < 0) {
                    o = 0;
                }
                if (o > prevO) {
                    prevX = itemX;
                    prevO = o;
                }
            }
            
            if (prevO > 0) {
                let s = scrollX + prevX;
                
                if (s < bodyItemW && lockScrollLeft) {
                    $('.body-page-main').css('overflow-x', 'hidden');
                    $('.body-page-main').stop().animate({scrollLeft: bodyItemW}, 250, () => {
                        $('.body-page-main').css('overflow-x', 'auto');
                    });
                } else
                if (s > bodyItemW && lockScrollRight) {
                    $('.body-page-main').css('overflow-x', 'hidden');
                    $('.body-page-main').stop().animate({scrollLeft: bodyItemW}, 250, () => {
                        $('.body-page-main').css('overflow-x', 'auto');
                    });
                } else {
                    $('.body-page-main').stop().animate({scrollLeft: s}, 250, () => {
                        let page = 'center';
                        if (s < bodyItemW) {
                            page = 'left';
                        } else
                        if (s > bodyItemW) {
                            page = 'right';
                        }

                        switch (page) {
                            case 'left':
                                history.back();
                                $('.body-page-main > div').css('opacity', 0);
                                break;
                            case 'center':
                                break;
                            case 'right':
                                window.location = '{{ route("terminal.checked") }}';
                                $('.body-page-main > div').css('opacity', 0);
                                break;
                        }
                    });
                }
            }
        }
    }
    
    function bodyViewCheckAutoscroll() {
        bodyTimeOutForScroll = setTimeout(() => {
            let s = $('.body-page-main').scrollLeft();
            if (s == bodyLastScroll) {
                bodyViewCentringItem();
            } else {
                bodyLastScroll = s;
                bodyViewCheckAutoscroll();
            }
        }, 100);
    }
    
    function recalcSpinerPos() {
        let t = $(window).scrollTop() + $(window).height() / 2 - $('nav').height() / 2;
        $('.body-page-left').css('padding-top', t);
        $('.body-page-right').css('padding-top', t);
    }
</script>

@endsection