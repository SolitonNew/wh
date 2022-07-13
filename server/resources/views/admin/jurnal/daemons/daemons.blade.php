@extends('admin.jurnal.jurnal')

@section('page-down-menu')
<a href="#" class="dropdown-item" onclick="daemonStart(); return false">@lang('admin/jurnal.daemon_run')</a>
<a href="#" class="dropdown-item" onclick="daemonStop(); return false;">@lang('admin/jurnal.daemon_stop')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="daemonReload(); return false;">@lang('admin/jurnal.daemon_reload')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="daemonStartAll(); return false">@lang('admin/jurnal.daemon_run_all')</a>
<a href="#" class="dropdown-item" onclick="daemonStopAll(); return false">@lang('admin/jurnal.daemon_stop_all')</a>
@endsection

@section('page-content')
<style>
    #daemonsList .start,
    #daemonsList .started .stop {
        display: none;
    }
    
    #daemonsList .stop,
    #daemonsList .started .start {
        display: inline;
    }
    
    .main-content {
        display: flex;
        flex-direction: column;
    }
    
    .bold {
        font-weight: bold;
    }
</style>
<div id="daemonListCompact" class="navbar navbar-page" style="display: none;">
    <select id="daemonListCombobox" class="nav-link custom-select select-tree" style="width: 100%;">
        @foreach($daemons as $row)
        <option value="{{ $row->id }}" 
                {{ $row->id == $id ? 'selected' : '' }}></option>
        @endforeach
    </select>
</div>
<div style="position:relative; display: flex; flex-direction: row; height: 100%;">
    <div id="daemonsList" class="tree" 
         style="width: 250px; min-width:250px; border-right: 1px solid rgba(0,0,0,0.125); justify-content: space-between;" 
         scroll-store="jurnalDaemonsList">
        @foreach($daemons as $row)
        <a href="{{ route('admin.jurnal-daemons', ['id' => $row->id]) }}"
            class="tree-item {{ $row->id == $id ? 'active' : '' }} {{ $row->stat ? 'started' : '' }}"
            data-id="{{ $row->id }}"
            style="display: block;">
            <div style="display: flex;">
                <div style="flex-grow: 1;">{{ $row->id }}</div>
                <div class="badge badge-pill badge-success start" style="margin-top: 0; margin-bottom: 0px;">RUN</div>
                <div class="badge badge-pill badge-warning stop" style="margin-top: 0; margin-bottom: 0px;">STOP</div>
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
        
        // Compact Navigate
        $('#daemonListCombobox').on('change', function () {
            window.location.href = '{{ route("admin.jurnal-daemons", ["id" => ""]) }}/' + $(this).val();
        });
        
        daemonCompactListUpdate();
    });
    
    function daemonListChangeState(data) {
        for (let i = 0; i < data.length; i++) {
            if (data[i].stat) {
                $('#daemonsList .tree-item[data-id="' + data[i].id + '"]').addClass('started');
            } else {
                $('#daemonsList .tree-item[data-id="' + data[i].id + '"]').removeClass('started');
            }
        }
        
        daemonCompactListUpdate();
    }
    
    function daemonCompactListUpdate() {
        $('#daemonListCombobox option').each(function () {
            let row = $('#daemonsList .tree-item[data-id="' + $(this).val() + '"]');
            let text = $('small', row).text();
            if (row.hasClass('started')) {
                text = '[R] ' + text;
            } else {
                text = '[ ] ' + text;
            }
            $(this).html(text);
        });
    }

    function daemonStart() {
        confirmYesNo("@lang('admin/jurnal.daemon_run_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-daemon-start", ["id" => $id]) }}').done((data) => {
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
            $.ajax('{{ route("admin.jurnal-daemon-stop", ["id" => $id]) }}').done((data) => {
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
            $.ajax('{{ route("admin.jurnal-daemon-restart", ["id" => $id]) }}').done((data) => {
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
            url: '{{ route("admin.jurnal-daemon-data", ["id" => $id, "lastID" => ""]) }}/' + daemonLogLastID,
            success: function (data) {
                if (data) {
                    let lines = $(data);
                    let count = lines.length;
                    if (count) {
                        $('.daemon-log-offset').css('top', '0px');
                        
                        let prevProgress = $('.daemon-log-offset > div:first').first().text().indexOf('PROGRESS:') == 0;
                        let nextProgress = lines.last().text().indexOf('PROGRESS:') == 0;
                        let nextFirstProgress = lines.first().text().indexOf('PROGRESS:') == 0;
                        
                        let nowProgress = false;
                        if (prevProgress && nextProgress) {
                            $('.daemon-log-offset > div:first').remove();
                            nowProgress = (count == 1);
                            count--;
                        }
                        
                        $('.daemon-log-offset').prepend(lines);
                        daemonLogLastID = $(lines.first()).data('id');
                        
                        if ((!daemonLogStart && nextFirstProgress) || (nowProgress || nextFirstProgress)) {
                            daemonLogLastID--;
                        }
                        
                        $('.daemon-log-offset > div:gt({{ config("settings.admin_daemons_log_lines_count") }})').remove();
                        
                        $('.daemon-log-offset > div').each(function () {
                            if ($(this).text().indexOf('PROGRESS:') == 0) {
                                makeProgressForElement(this);
                            }
                        });
                    }
                    
                    if (!daemonLogStart || $('.content-body').scrollTop() > 0) {
                        
                    } else {                        
                        if (count) {
                            $('.daemon-log-offset').stop(true);
                            let h = 0;
                            $('.daemon-log-offset > div:lt(' + count + ')').each(function () {
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
        
        function makeProgressForElement(element) {
            let div = $(element);
            let percent = div.text().split(':')[1];
            
            if (percent > 97) percent = 100;
            if (percent > 100) percent = 100;
            
            let control = $('.progress', element);
            
            if (control.length == 0) { 
                let html = '<div class="progress-bg">' +
                           '<div class="progress">' + 
                           '<div class="progress-bar" role="progressbar" style="width: ' + percent + '%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                           '</div>' +
                           '</div>'
                div.append(html);
            } else {
                
            }
        }
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