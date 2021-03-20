@extends('dialog')

@section('title')
@lang('admin/configuration.ow_info_title')
@endsection

@section('content')
<form id="configuration_ow_form" class="container" method="POST">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_CONTROLLER')</div>
        </div>
        <div class="col-sm-6">
            <div class="form-control">{{ $item->controller_name }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_ROM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->rom }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_COMM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->comm }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_CHANNELS')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->channels }}</div>
        </div>
    </div>
    <div class="form-group">
        <div class="">@lang('admin/configuration.table_VARIABLES') ({{ count($item->variables) }}):</div>
        <div class="form-control" style="height: auto;">
        @forelse($item->variables as $v)
        <div>[{{ $v->channel }}] {{ $v->name }}</div>
        @empty
        -//-
        @endforelse
        </div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="configurationOwDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        //
    });

    function configurationOwDelete() {
        confirmYesNo("@lang('admin/configuration.ow-delete-confirm')", () => {
            $.ajax('{{ route("configuration-ow-delete", $item->id) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {

                }
            });
        });
    }

</script>
@endsection
