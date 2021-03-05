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
            <div class="form-control">{{ $item->ID }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_CONTROLLER')</div>
        </div>
        <div class="col-sm-6">
            <div class="form-control">{{ $item->CONTROLLER_NAME }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_ROM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->ROM }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_COMM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->COMM }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.table_CHANNELS')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->CHANNELS }}</div>
        </div>
    </div>
    <div class="form-group">
        <div class="">@lang('admin/configuration.table_VARIABLES') ({{ count($item->VARIABLES) }}):</div>
        <div class="form-control" style="height: auto;">
        @forelse($item->VARIABLES as $v)
        <div>[{{ $v->CHANNEL }}] {{ $v->NAME }}</div>
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
            $.ajax('{{ route("configuration-ow-delete", $item->ID) }}').done((data) => {
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
