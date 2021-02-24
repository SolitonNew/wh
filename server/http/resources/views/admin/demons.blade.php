@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="demonRun()">@lang('admin\demons.demon_run')</a>
<a href="#" class="dropdown-item" onclick="demonStop()">@lang('admin\demons.demon_stop')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="demonReload()">@lang('admin\demons.demon_reload')</a>
@endsection

@section('top-menu')
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="demonsList">
        @foreach($demons as $key)
        <a href="{{ route('demons', $key) }}"
            class="tree-item {{ $key == $id ? 'active' : '' }}">
            <div style="flex-grow: 1; display: flex; flex-direction: column;align-items: stretch;">
                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div class="" style="flex-grow: 1; ">{{ $key }}</div>
                    <div class="badge badge-pill badge-success" style="margin-top: 0; margin-bottom: 0px;">RUN</div>
                </div>
                <small class="text-muted">@lang('admin\demons.'.$key)</small>
            </div>
        </a>
        @endforeach
    </div>
    <div class="content-body" style="padding: 1rem; font-family: 'Courier New'" scroll-store="demonsContentScroll">

    </div>
</div>

<script>
    let demonLogLastID = -1;

    $(document).ready(() => {
        getDemonData();
    });

    function demonRun() {
        confirmYesNo("@lang('admin\demons.demon_run_confirm')", () => {
            $.ajax('{{ route("demon-start", $id) }}').done((data) => {

            });
        });
    }

    function demonStop() {
        confirmYesNo("@lang('admin\demons.demon_stop_confirm')", () => {
            $.ajax('{{ route("demon-stop", $id) }}').done((data) => {

            });
        });
    }

    function demonReload() {
        confirmYesNo("@lang('admin\demons.demon_reload_confirm')", () => {
            $.ajax('{{ route("demon-restart", $id) }}').done((data) => {

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
                }
                setTimeout(getDemonData, 500);
            },
            error: function () {
                console.log('ERROR');
                setTimeout(getDemonData, 3000);
            },
        });
    }

</script>
@endsection
