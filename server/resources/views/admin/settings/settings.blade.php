@extends('admin.admin')

@section('down-menu')
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
            @include('admin.settings.din-settings')
            @include('admin.settings.pyhome-settings')
            @include('admin.settings.forecast')
        </div>
        <div class="col-sm-6">
            @include('admin.settings.location')
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
</script>
@endsection
