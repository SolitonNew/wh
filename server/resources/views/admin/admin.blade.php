@extends('index')

@section('head')
<link rel="stylesheet" href="/css/admin.css?v={{ str_replace(' ', '_', App\Models\Property::VERSION) }}">
<link rel="stylesheet" href="/css/script-editor.css">
@endsection

@section('body')
<div class="body-container
            {{ isset($_COOKIE['SHOW_LOG']) && $_COOKIE['SHOW_LOG'] == 'true' ? 'show-log' : '' }}
            {{ isset($_COOKIE['SIZE_MAIN_MENU']) && $_COOKIE['SIZE_MAIN_MENU'] == 'small' ? 'main-menu-collapse' : '' }}" style="opacity: 0;">
    <div class="body-content">
        <div class="main-menu">
            <nav class="navbar">
                <div class="logo">WATCH HOUSE</div>
                <div class="version">@lang('admin/admin.version') {{ App\Models\Property::VERSION }}</div>
                <div class="logo-mini">WH</div>
                <div class="navbar-down-menu">
                    <div class="btn-group">
                        <button type="button" id="sizeMainMenu" class="btn btn-secondary">
                            <img src="/img/menus/menu-3x.png" style="margin: -5px -4px 0px;">
                        </button>
                    </div>
                    <div id="menuActionGroup" class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle"
                                data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                            <img src="/img/menus/list-rich-2x.png">
                            <span class="btn-label">@lang('admin/admin.menu_actions')</span>
                        </button>
                        <div class="dropdown-menu">
                            @yield('down-menu')
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1"></div>
                @if(\App\Models\Hub::existsFirmwareHubs() && \App\Models\Property::getFirmwareChanges() > 0)
                <a class="btn btn-danger" href="#" onclick="firmware(); return false;">
                    <img src="/img/menus/bolt-2x.png">
                    <span class="btn-label">@lang('admin/admin.menu_firmware')</span>
                </a>
                @endif
                <a class="btn btn-primary" href="{{ route('home') }}" target="_blank">
                    <img src="/img/menus/phone-2x.png">
                    <span class="btn-label">@lang('admin/admin.menu_home')</span>
                </a>
                <a class="btn btn-secondary" href="{{ route('logout') }}">
                    <img src="/img/menus/account-logout-2x.png">
                    <span class="btn-label">@lang('admin/admin.menu_logout')</span>
                </a>
                <a id="showLog" class="btn btn-primary" href="#" style="margin-right: 0;">
                    <img src="/img/menus/list-2x.png">
                    <span class="btn-label">@lang('admin/admin.menu_show_log')</span>
                </a>
                <a id="hideLog" class="btn btn-primary" href="#" style="margin-right: 0;">
                    <img src="/img/menus/list-2x.png">
                    <span class="btn-label">@lang('admin/admin.menu_hide_log')</span>
                </a>
                <div class="navbar-top-menu">
                @yield('top-menu')
                </div>
            </nav>
        </div>
        <div class="main-container">
            <div class="main-left-panel">
                <div class="main-left-panel-container">
                    <div class="list-group">
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'plan') }}" href="{{ route('admin.plan') }}">
                            <img src="/img/menus/clipboard-2x.png">
                            <span class="label">@lang('admin/plan.menu')</span>
                            <span class="badge badge-primary badge-pill">{{ \App\Models\Room::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'hubs') }}" href="{{ route('admin.hubs') }}">
                            <img src="/img/menus/pulse-2x.png">
                            <span class="label">@lang('admin/hubs.menu')</span>
                            <span class="badge badge-primary badge-pill">{{ \App\Models\Device::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'scripts') }}" href="{{ route('admin.scripts') }}">
                            <img src="/img/menus/document-2x.png">
                            <span class="label">@lang('admin/scripts.menu')</span>
                            <span class="badge badge-primary badge-pill">{{ \App\Models\Script::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'schedule') }}" href="{{ route('admin.schedule') }}">
                            <img src="/img/menus/calendar-2x.png">
                            <span class="label">@lang('admin/schedule.menu')</span>
                            <span class="badge badge-primary badge-pill">{{ \App\Models\Schedule::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'jurnal') }}" href="{{ route('admin.jurnal') }}">
                            <img src="/img/menus/bar-chart-2x.png">
                            <span class="label">@lang('admin/jurnal.menu')</span>
                            <span id="daemonsState" class="badge badge-primary badge-pill">0 / 0</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'users') }}" href="{{ route('admin.users') }}">
                            <img src="/img/menus/people-2x.png">
                            <span class="label">@lang('admin/users.menu')</span>
                            <span class="badge badge-primary badge-pill">{{ \App\Models\User::count() }}</span>
                        </a>
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ active_segment(2, 'settings') }}" href="{{ route('admin.settings') }}">
                            <img src="/img/menus/cog-2x.png">
                            <span class="label">@lang('admin/settings.menu')</span>
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
    @include('admin.log', ['logLastID' => -1])
    </div>
    <a id="hideLogMobile" class="btn btn-dark" href="#" style="margin-right: 0;">
        <img src="/img/menus/x-2x.png">
    </a>
</div>

<div class="modal fade dialog-background" id="dialog_window" tabindex="-1" role="dialog"
     aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content" id="dialog_content"></div>
    </div>
</div>

<div class="modal fade dialog-background" id="confirm_window" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog confirm-width" role="document" style="padding: 1rem;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirm_title" ></h5>
                <button type="button" class="close" onclick="confirmNo()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirm_text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm_btn_yes" onclick="confirmYes();"></button>
                <button type="button" class="btn btn-secondary" id="confirm_btn_no" onclick="confirmNo()"></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dialog-background" id="alert_window" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog confirm-width" role="document" style="padding: 1rem;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('dialogs.alert_title')</h5>
                <button type="button" class="close" onclick="alertOk()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="alert_text" style="white-space: pre;overflow-x: auto;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="alertOk();">@lang('dialogs.btn_ok')</button>
            </div>
        </div>
    </div>
</div>

<div id="globalWaiter" class="form-waiter" style="position:fixed;">
    <div>
        <div class="spinner-border text-primary" style="width: 5rem;height: 5rem;">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<script src="/js/jquery.form.js"></script>
<script src="/js/script-editor.js"></script>

<script>
    var serverTimeOffset = (new Date()).getTime() / 1000 - {{ \Carbon\Carbon::now('UTC')->timestamp }};

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
        requestDaemonsState();

        $('.body-container').css({opacity: 1});

        $('div[scroll-store]').each(function () {
            let name = $(this).attr('scroll-store');
            let c = getCookie(name);
            if (c) {
                let a = c.split('|');
                if (a[0] > 0) {
                    $(this).scrollTop(a[0]);
                }
                if (a[1] > 0) {
                    $(this).scrollLeft(a[1]);
                }
            }

            $(this).on('scroll', (e) => {
                let o = $(this);
                setCookie(name, o.scrollTop() + '|' + o.scrollLeft());
            }).trigger('scroll');
        });

        // Log controls ---------

        $('#showLog').on('click', function () {
            $('.body-container').addClass('show-log');
            $('.body-content-log')
                .css({width: '0px', 'min-width': '0px'})
                .animate({width: '370px', 'min-width': '370px'}, $(window).width() > 768 ? 250 : 1, function () {
                    $(window).trigger('resize');
                });
            setCookie('SHOW_LOG', 'true');
            return false;
        });

        $('#hideLog, #hideLogMobile').on('click', function () {
            $('.body-content-log')
                .css({width: '370px', 'min-width': '370px'})
                .animate({width: '0px', 'min-width': '0px'}, $(window).width() > 768 ? 250 : 1, function () {
                    $('.body-container').removeClass('show-log');
                    $(window).trigger('resize');
                });
            setCookie('SHOW_LOG', 'false');
            return false;
        });

        // Global Waiter

        $('a').on('click', function () {
            let href = $(this).attr('href');
            let target = $(this).attr('target');
            if (href
                && href != '#'
                && (href.indexOf('javascript') < 0)
                && target != '_blank')
            {
                startGlobalWaiter();
            }
        });

        // Main menu

        $('#sizeMainMenu').on('click', function () {
            $('.body-container').toggleClass('main-menu-collapse');
            setCookie('SIZE_MAIN_MENU', $('.body-container').hasClass('main-menu-collapse') ? 'small' : 'large');
            setTimeout(function () {
                $(window).trigger('resize');
            }, 300);
        });

        // Main menu actions

        if ($('#menuActionGroup .dropdown-menu > .dropdown-item').length == 0) {
            $('#menuActionGroup > button').addClass('disabled');
        }

        // ----------------------

        if ($('.navbar-top-menu .nav-link').length) {
            $('.main-menu .navbar').addClass('navbar-with-tabs');
        }

        $(window).on('resize', function () {
            if ($(this).width() < 758) {
                if (!$('.body-container').hasClass('main-menu-collapse')) {
                    $('#sizeMainMenu').trigger('click');
                }
            }
        }).trigger('resize');
    });

    function dialog(url, beforeHandler, afterHandler) {
        startGlobalWaiter();
        dialogAfterHandler = afterHandler;
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

    var confirmHandler = false;
    var confirmNoHandler = false;

    function confirm(title, text, yes, no, handler, noHandler) {
        confirmHandler = handler;
        confirmNoHandler = noHandler;

        $('#confirm_title').text(title);
        $('#confirm_text').html(text);
        $('#confirm_btn_yes').text(yes);
        $('#confirm_btn_no').text(no);
        $('#confirm_window').modal({backdrop: 'static', keyboard: false});
    }

    function confirmYes() {
        confirmHandler();
        $('#confirm_window').modal('hide');
    }

    function confirmNo() {
        if (confirmNoHandler) {
            confirmNoHandler();
        }
        $('#confirm_window').modal('hide');
    }

    function confirmYesNo(text, handler, noHandler) {
        confirm("@lang('dialogs.confirm_title')",
                text,
                "@lang('dialogs.btn_yes')",
                "@lang('dialogs.btn_no')",
                handler, noHandler);
    }

    var alertHandler = false;

    function alert(text, handler) {
        alertHandler = handler;
        $('#alert_text').html(text);
        $('#alert_window').modal({backdrop: 'static', keyboard: false});
    }

    function alertOk() {
        if (alertHandler) {
            alertHandler();
        }
        $('#alert_window').modal('hide');
    }


    var globalWaiter = false;

    function startGlobalWaiter() {
        $('#globalWaiter').show();
    }

    function stopGlobalWaiter() {
        if (globalWaiter) {
            clearTimeout(globalWaiter);
        }
        $('#globalWaiter').hide();
    }

    function reloadWithWaiter(url = '') {
        startGlobalWaiter();
        if (url) {
            window.location.href = url;
        } else {
            window.location.reload();
        }
    }

    let lastDeviceChangeID = -1;

    function calcLastVariableID() {
        let ls = $('.log-row');
        if (ls.length > 0) {
            lastDeviceChangeID = $(ls[0]).attr('data-id');
        }
    }

    function loadVariableChanges() {
        $.ajax('{{ route("admin.variable-changes", ["lastID" => ""]) }}/' + lastDeviceChangeID).done((data) => {
            if (data && (data.substr(0, 15) == '<!DOCTYPE HTML>')) {
                window.location.reload();
                return ;
            }

            if (data) {
                let ls = $(data);
                $('#logList').prepend(ls.hide());
                ls.slideToggle(250);
                calcLastVariableID();

                /*  Call handler if registered  */
                if (window.variableChangesHandler) {
                    variableChangesHandler(data);
                }
                /*  --------------------------------------------------------  */

                $('.log-row:gt({{ config("settings.admin_log_lines_count") }})').remove();
            }

            setTimeout(loadVariableChanges, {{ config("settings.admin_log_update_interval") }});
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
        headerContainer = $('<div class="table-scroll-header-container"></div>');
        headerContainer.append(header);
        headerContainer.insertAfter(table);

        let parent = $(table.parent());

        parent.on('scroll', (e) => {
            header.css({
                left: -parent.scrollLeft() + 'px',
            });
        });

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

            let offset = parent.offset();
            if (offset) {
                let r = header[0].getBoundingClientRect();
                headerContainer.css({
                    left: offset.left + 'px',
                    top: offset.top + 'px',
                    width: parent[0].clientWidth + 'px',
                    height: r.height + 'px',
                });
            }

            parent.trigger('scroll');
        }).trigger('resize');
    }

    function resetScrollStore(obj) {
        let name = $(obj).attr('scroll-store');
        if (name) {
            setCookie(name, '0');
        }
    }

    function firmware() {
        dialog("{{ route('admin.hubs-config-wizard') }}", null, () => {
            if (window.configWizardSuccess) {
                reloadWithWaiter();
            }
        });
    }

    function requestDaemonsState() {
        $.ajax('{{ route("admin.jurnal-daemons-state") }}').done((data) => {
            if (typeof(data) == 'string') {
                if (data && (data.substr(0, 15) == '<!DOCTYPE HTML>')) {
                    window.location.reload();
                    return ;
                }
            }

            if (data) {
                let count = data.length;
                let enabled = 0;
                for (let i = 0; i < count; i++) {
                    if (data[i].stat) {
                        enabled++;
                    }
                }
                $('#daemonsState').text(enabled + ' / ' + count);

                if (window.daemonListChangeState) {
                    daemonListChangeState(data);
                }
            }

            setTimeout(requestDaemonsState, {{ config("settings.admin_daemins_status_update_interval") }});
        });
    }
</script>
@endsection
