@extends('admin.jurnal.jurnal')

@section('page-down-menu')
<a href="#" class="dropdown-item {{ $stat ? 'disabled' : '' }}" onclick="demonStart(); return false">@lang('admin/jurnal.demon_run')</a>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonStop(); return false;">@lang('admin/jurnal.demon_stop')</a>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonReload(); return false;">@lang('admin/jurnal.demon_reload')</a>
@endsection

@section('page-content')
<div style="position:relative; display: flex; flex-direction: row; height: 100%;">
    <div class="tree" 
         style="width: 250px; min-width:250px; border-right: 1px solid rgba(0,0,0,0.125); justify-content: space-between;" 
         scroll-store="jurnalDemonsList">
        @foreach($demons as $row)
        <a href="{{ route('admin.jurnal-demons', $row->id) }}"
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
            <small class="text-muted" style="white-space: normal;">@lang('admin/demons/'.$row->id.'.title')</small>
        </a>
        @endforeach
    </div>
    <div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;position: relative;">
        <div class="content-body" style="padding: 1rem;" scroll-store="demonsContentScroll">
            <div class="demon-log" style="position: relative;">
                <div class="demon-log-offset" style="position: absolute;"></div>
            </div>
        </div>
        <button class="demon-log-btn-top btn btn-primary" style="display: none;" onclick="demonLogScrollTop()">@lang('admin/jurnal.demon_btn_top')</button>
    </div>
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
        confirmYesNo("@lang('admin/jurnal.demon_run_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-demon-start", $id) }}').done((data) => {
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
        confirmYesNo("@lang('admin/jurnal.demon_stop_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-demon-stop", $id) }}').done((data) => {
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
        confirmYesNo("@lang('admin/jurnal.demon_reload_confirm')", () => {
            startGlobalWaiter();
            $.ajax('{{ route("admin.jurnal-demon-restart", $id) }}').done((data) => {
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
            url: '{{ route("admin.jurnal-demon-data", [$id, ""]) }}/' + demonLogLastID,
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