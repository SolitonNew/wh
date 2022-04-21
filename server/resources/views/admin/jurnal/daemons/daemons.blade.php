@extends('admin.jurnal.jurnal')

@section('page-down-menu')
<a href="#" class="dropdown-item {{ $stat ? 'disabled' : '' }}" onclick="daemonStart(); return false">@lang('admin/jurnal.daemon_run')</a>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="daemonStop(); return false;">@lang('admin/jurnal.daemon_stop')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="daemonReload(); return false;">@lang('admin/jurnal.daemon_reload')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="daemonStartAll(); return false">@lang('admin/jurnal.daemon_run_all')</a>
<a href="#" class="dropdown-item" onclick="daemonStopAll(); return false">@lang('admin/jurnal.daemon_stop_all')</a>
@endsection

@section('page-content')
<div style="position:relative; display: flex; flex-direction: row; height: 100%;">
    <div id="daemonsList" class="tree" 
         style="width: 250px; min-width:250px; border-right: 1px solid rgba(0,0,0,0.125); justify-content: space-between;" 
         scroll-store="jurnalDaemonsList">
        @foreach($daemons as $row)
        <a href="{{ route('admin.jurnal-daemons', $row->id) }}"
            class="tree-item {{ $row->id == $id ? 'active' : '' }}"
            style="display: block;">
            <div style="display: flex;">
                <div style="flex-grow: 1;">{{ $row->id }}</div>
                @if ($row->stat)
                <div class="badge badge-pill badge-success" style="margin-top: 0; margin-bottom: 0px;">RUN</div>
                @else
                <div class="badge badge-pill badge-warning" style="margin-top: 0; margin-bottom: 0px;">STOP</div>
                @endif
            </div>
            <small class="text-muted" style="white-space: normal;">@lang('admin/daemons/'.$row->id.'.title')</small>
        </a>
        @endforeach
    </div>
    <div id="daemonViewer" style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;position: relative;">
        <div class="content-body" style="padding: 1rem;" scroll-store="daemonsContentScroll">
            <div class="daemon-log" style="position: relative;">
                <div class="daemon-log-offset" style="position: absolute;"></div>
            </div>
        </div>
        <button class="daemon-log-btn-top btn btn-primary" style="display: none;" onclick="daemonLogScrollTop()">@lang('admin/jurnal.daemon_btn_top')</button>
    </div>
</div>

<script>
    let daemonLogLastID = -1;
    let daemonLogStart = false;

    $(document).ready(() => {
        getDaemonData();
        
        $('.content-body').on('scroll', function () {
            if ($(this).scrollTop() == 0) {
                $('.daemon-log-btn-top').fadeOut(250);
            } else {
                $('.daemon-log-btn-top').fadeIn(250);
            }
        });
    });

    function daemonStart() {
        confirmYesNo("@lang('admin/jurnal.daemon_run_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-start", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function daemonStop() {
        confirmYesNo("@lang('admin/jurnal.daemon_stop_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-stop", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function daemonReload() {
        confirmYesNo("@lang('admin/jurnal.daemon_reload_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-restart", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function getDaemonData() {
        $.ajax({
            url: '{{ route("admin.jurnal-daemon-data", [$id, ""]) }}/' + daemonLogLastID,
            success: function (data) {
                if (data) {
                    let lines = $(data);
                    $('.daemon-log-offset').prepend(lines);
                    let i = lines.length;
                    if (i > 0) {
                        $('.daemon-log-offset').css('top', '0px');
                        $('.daemon-log-offset').prepend(lines);
                        daemonLogLastID = $(lines.first()).data('id');
                        $('.daemon-log-offset > div:gt({{ config("app.admin_daemons_log_lines_count") }})').remove();
                    }
                    
                    if (!daemonLogStart || $('.content-body').scrollTop() > 0) {
                        
                    } else {                        
                        if (i > 0) {
                            $('.daemon-log-offset').stop(true);
                            let h = 0;
                            $(lines).each(function () {
                                h += $(this).height();
                            });

                            let t = $('.daemon-log-offset').position().top;
                            $('.daemon-log-offset').css('top', (t - h) + 'px');
                            $('.daemon-log-offset').animate({
                                top: '0px',
                            }, 300);
                        }
                    }
                }
                daemonLogStart = true;
                setTimeout(getDaemonData, 250);
            },
            error: function () {
                console.log('ERROR');
                setTimeout(getDaemonData, 3000);
            },
        });
    }
    
    function daemonLogScrollTop() {
        $('.content-body').scrollTop(0);
    }
    
    function daemonStartAll() {
        confirmYesNo("@lang('admin/jurnal.daemon_run_all_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-start-all") }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }
    
    function daemonStopAll() {
        confirmYesNo("@lang('admin/jurnal.daemon_stop_all_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-stop-all") }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

</script>
@endsection