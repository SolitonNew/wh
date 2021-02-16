@extends('index')

@section('head')
<link rel="stylesheet" href="/css/admin.css">
<script src="/js/jquery.form.js"></script>
@endsection

@section('body')
<div class="body-container" style="opacity: 0;">
    <div class="body-content">
        <div class="main-menu">
            <nav class="navbar">
                <div class="logo">WISE HOUSE</div>
                @yield('top-menu')
            </nav>
        </div>
        <div class="main-container">
            <div class="main-left-panel">
                <div class="main-left-panel-container">
                    <div style="height: 130px;padding: 0.5rem;text-align: center;">
                        <img src="/img/logo.png" height="100%">
                    </div>
                    <div class="list-group">
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('rooms')" href="{{ route('parts') }}">
                            @lang('admin/rooms.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\PlanPartsModel::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('variables')" href="{{ route('variables') }}">
                            @lang('admin/variables.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\VariablesModel::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('scripts')" href="{{ route('scripts') }}">
                            @lang('admin/scripts.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\ScriptsModel::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('statistics')" href="{{ route('statistics') }}">
                            @lang('admin/statistics.menu')
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('users')" href="{{ route('users') }}">
                            @lang('admin/users.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\UsersModel::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('ow-manager')" href="{{ route('ow-manager') }}">
                            @lang('admin/ow-manager.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\OwDevsModel::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @activeMenu('schedule')" href="{{ route('schedule') }}">
                            @lang('admin/schedule.menu')
                            <span class="badge badge-primary badge-pill">{{ \App\Http\Models\SchedulerModel::count() }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>
    <div id="logList" class="body-content-log">
    @include('admin.log')
    </div>
</div>

<div class="modal fade" id="dialog_window" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" id="dialog_content">AAAAAAAAAA</div>
    </div>
</div>

<div id="globalWaiter" class="form-waiter" style="position:fixed;">
    <div class="modal-content form-waiter-contaner" style="max-width:350px;width:100%;margin-top: 20vh;padding: 20px 30px">
        <div class="form-waiter-label">@lang('dialogs.processed')</div>
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" 
                 style="width: 100%"></div>
        </div>
    </div>
</div>

<script>
    $('document').ready(() => {
        convertTableToScrollGrid($('.table-fixed-header'));
        
        $('#dialog_window').on('hidden.bs.modal', function (e) {
            $('#dialog_content').html('');
            if (dialogAfterHandler) {
                dialogAfterHandler();
            }
        });
        
        calcLastVariableID();
        loadVariableChanges();
        
        $('.body-container').css({opacity: 1});
        
        $('div[scroll-store]').each(function () {
            let name = $(this).attr('scroll-store');
            let t = getCookie(name);
            if (t > 0) {
                $(this).scrollTop(t);
            }
            
            $(this).on('scroll', (e) => {
                let o = $(this);
                document.cookie = name + '=' + o.scrollTop() + '; path=/admin; max-age=3600';
            }).trigger('scroll');
        });
    });
    
    function dialog(url, beforeHandler) {
        startGlobalWaiter();
        dialogAfterHandler = false;
        $('#dialog_window .modal-sm').removeClass('modal-sm');
        $.ajax({url:url}).done(function (data) {
            stopGlobalWaiter();
            if (beforeHandler)
                beforeHandler();
            $('#dialog_content').html(data);
            $('#dialog_window').modal('show');
        });
    }

    function dialogHide(afterHandler) {
        dialogAfterHandler = afterHandler;
        $('#dialog_window').modal('hide');
    }
    
    var globalWaiter = false;

    function startGlobalWaiter() {
        $('#globalWaiter').show();
        $('#globalWaiter').css('opacity', 0);
        globalWaiter = setTimeout(function () {
            $('#globalWaiter').fadeTo(1000, 1);
        }, 500);            
    }

    function stopGlobalWaiter() {
        if (globalWaiter) {
            clearTimeout(globalWaiter);
        }
        $('#globalWaiter').hide();
    }
    
    let lastVariableID = -1;
    
    function calcLastVariableID() {
        let ls = $('.log-row');
        if (ls.length > 0) {
            lastVariableID = $(ls[0]).attr('data-id');
        }
    }
    
    function loadVariableChanges() {
        $.ajax('{{ route("variable-changes", "") }}/' + lastVariableID).done((data) => {
            $('#logList').prepend(data);
            calcLastVariableID();
           
            /*  Вызываем обработчик, если он зарегистрирован на странице  */
            if (window.variableChangesHandler) {
                variableChangesHandler(data);
            }           
            /*  --------------------------------------------------------  */
           
            setTimeout(loadVariableChanges, {{ config("app.admin_log_update_interval") }});
           
            $('.log-row:gt({{ config("app.admin_log_lines_count") }})').remove();
        });
    }
    
    function variableChangesParse(data) {
        let res = new Array();
        $(data).each(() => {
            let o = $(this);
            res.push({
                varID: o.attr('data-varID'),
                value: o.attr('data-value'),
            });
        });
        return res;
    }
    
    
    function convertTableToScrollGrid(table) {
        let header = $('<table><thead>' + $('thead', table).html() + '</thead></table>');
        header.attr('class', table.attr('class') + ' table-scroll-header');
        header.insertAfter(table);
        
        let parent = $(table.parent());
        parent.on('scroll', (e) => {
            header.css({
                top: parent.scrollTop() + 'px',
            });
        })
                
        $(window).on('resize', () => {
            header.width(table.width());
            
            let th_p = $('thead tr th', table);
            let th_h = $('thead tr th', header);

            for (let i = 0; i < th_p.length; i++) {
                let w = $(th_p[i]).width() + 2 + 'px';
                $(th_h[i]).css({
                    'width': w,
                });
            }
        }).trigger('resize');
    }
    
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
    
</script>
@endsection