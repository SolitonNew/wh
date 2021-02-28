@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item {{ $stat ? 'disabled' : '' }}" onclick="demonStart(); return false">@lang('admin/demons.demon_run')</a>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonStop(); return false;">@lang('admin/demons.demon_stop')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item {{ $stat ? '' : 'disabled' }}" onclick="demonReload(); return false;">@lang('admin/demons.demon_reload')</a>
@endsection

@section('top-menu')
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="demonsList">
        @foreach($demons as $row)
        <a href="{{ route('demons', $row->ID) }}"
            class="tree-item {{ $row->ID == $id ? 'active' : '' }}">
            <div style="flex-grow: 1; display: flex; flex-direction: column;align-items: stretch;">
                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div class="" style="flex-grow: 1; ">{{ $row->ID }}</div>
                    @if ($row->STAT)
                    <div class="badge badge-pill badge-success" style="margin-top: 0; margin-bottom: 0px;">RUN</div>
                    @else
                    <div class="badge badge-pill badge-warning" style="margin-top: 0; margin-bottom: 0px;">STOP</div>
                    @endif
                </div>
                <small class="text-muted">@lang('admin/demons.'.$row->ID)</small>
            </div>
        </a>
        @endforeach
    </div>
    <div class="content-body demon-log" scroll-store="demonsContentScroll">

    </div>
</div>

<script>
    let demonLogLastID = -1;

    $(document).ready(() => {
        getDemonData();
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
                    $('.content-body').prepend(data);
                    demonLogLastID = $($(data).first()).data('id');
                    
                    $('.content-body > div:gt({{ config("app.admin_demons_log_lines_count") }})').remove();
                }
                setTimeout(getDemonData, 250);
            },
            error: function () {
                console.log('ERROR');
                setTimeout(getDemonData, 3000);
            },
        });
    }

</script>
@endsection
