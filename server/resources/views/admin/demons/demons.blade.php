@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item {{ $stat ? 'disabled' : '' }}" onclick="demonStart(); return false">@lang('admin/demons.demon_run')</a>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonStop(); return false;">@lang('admin/demons.demon_stop')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonReload(); return false;">@lang('admin/demons.demon_reload')</a>
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    @foreach($demons as $row)
    <a class="nav-link upper {{ $row->id == $id ? 'active' : '' }}" href="{{ route('demons', $row->id) }}">
        <span style="margin-right: 0.5rem">{{ $row->id }}</span>
        @if ($row->stat)
        <div class="badge badge-pill badge-success" style="margin-top: 0; margin-bottom: 0px;">RUN</div>
        @else
        <div class="badge badge-pill badge-warning" style="margin-top: 0; margin-bottom: 0px;">STOP</div>
        @endif
    </a>
    @endforeach
</div>
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;position: relative;">
    <div class="content-body" style="padding: 1rem;" scroll-store="demonsContentScroll">
        <div class="demon-log" style="position: relative;">
            <div class="demon-log-offset" style="position: absolute;"></div>
        </div>
    </div>
    <button class="demon-log-btn-top btn btn-primary" style="display: none;" onclick="demonLogScrollTop()">@lang('admin/demons.demon_btn_top')</button>
</div>

<script>
    let demonLogLastID = -1;
    let demonLogStart = false;

    $(document).ready(() => {
        getDemonData();
        
        $('.content-body').on('scroll', function () {
            if ($(this).scrollTop() == 0) {
                $('.demon-log-btn-top').fadeOut(250);
            } else {
                $('.demon-log-btn-top').fadeIn(250);
            }
        });
    });

    function demonStart() {
        confirmYesNo("@lang('admin/demons.demon_run_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("demon-start", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function demonStop() {
        confirmYesNo("@lang('admin/demons.demon_stop_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("demon-stop", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function demonReload() {
        confirmYesNo("@lang('admin/demons.demon_reload_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("demon-restart", $id) }}').done((data) => {
                stopGlobalWaiter();
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    console.log(data);
                }
            });
        });
    }

    function getDemonData() {
        $.ajax({
            url: '{{ route("demon-data", [$id, ""]) }}/' + demonLogLastID,
            success: function (data) {
                if (data) {
                    let lines = $(data);
                    $('.demon-log-offset').prepend(lines);
                    let i = lines.length;
                    if (i > 0) {
                        $('.demon-log-offset').css('top', '0px');
                        $('.demon-log-offset').prepend(lines);
                        demonLogLastID = $(lines.first()).data('id');
                        $('.demon-log-offset > div:gt({{ config("app.admin_demons_log_lines_count") }})').remove();
                    }
                    
                    if (!demonLogStart || $('.content-body').scrollTop() > 0) {
                        
                    } else {                        
                        if (i > 0) {
                            $('.demon-log-offset').stop(true);
                            let h = 0;
                            $(lines).each(function () {
                                h += $(this).height();
                            });

                            let t = $('.demon-log-offset').position().top;
                            $('.demon-log-offset').css('top', (t - h) + 'px');
                            $('.demon-log-offset').animate({
                                top: '0px',
                            }, 300);
                        }
                    }
                }
                demonLogStart = true;
                setTimeout(getDemonData, 250);
            },
            error: function () {
                console.log('ERROR');
                setTimeout(getDemonData, 3000);
            },
        });
    }
    
    function demonLogScrollTop() {
        $('.content-body').scrollTop(0);
    }

</script>
@endsection
