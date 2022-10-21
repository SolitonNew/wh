@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="backupMetaImportDialog(); return false;">@lang('admin/settings.backup_meta_menu_import')</a>
<a href="{{ route('admin.backup-meta-export') }}" class="dropdown-item" target="_blank">@lang('admin/settings.backup_meta_menu_export')</a>
@endsection

@section('top-menu')
@endsection

@section('content')
<style>
    .card {
        margin-bottom: 1rem;
    }
</style>
<div class="content-body" style="margin: 1rem; margin-top: 0px; margin-right: 0px;" scroll-store="settingsList">
    <div class="row" style="margin: 1rem; margin-top: 2rem; margin-left: 0px;">
        <div class="col-sm-6">
            @include('admin.settings.timezone')
            @include('admin.settings.structure-deph')
            @if(App\Library\Daemons\DinDaemon::canRun())
            @include('admin.settings.din-settings')
            @endif
            @if(App\Library\Daemons\PyhomeDaemon::canRun())
            @include('admin.settings.pyhome-settings')
            @endif
            @if(App\Library\Daemons\ZigbeeoneDaemon::canRun())
            @include('admin.settings.zigbeeone-settings')
            @endif
            @if(App\Library\Daemons\ExtApiDaemon::canRun())
            @include('admin.settings.forecast')
            @endif
        </div>
        <div class="col-sm-6">
            @include('admin.settings.location')
            @include('admin.settings.check-update')
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#terminalMaxLevel input').on('input', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-max-level", ["value" => ""]) }}/' + $(this).data('value'),
                data: {

                },
                success: function (data) {
                    if (data == 'OK') {

                    } else {

                    }
                },
            });
        });
    });

    function backupMetaImportDialog() {
        dialog("{{ route('admin.backup-meta-import-show') }}");
    }
</script>
@endsection
