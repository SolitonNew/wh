@extends('dialog')

@section('title')
@lang('admin/hubs.host_edit_title')
@endsection

@section('content')
<form id="host_form" class="container" method="POST">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_CONTROLLER')</label>
        </div>
        <div class="col-sm-6">
            <div class="form-control">{{ $item->hub->name }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_ROM')</label>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->romAsString() }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_COMM')</label>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->type()->description }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/hubs.host_CHANNELS')</label>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ implode(', ', $item->type()->channels) }}</div>
        </div>
    </div>
    <div class="form-group">
        <label class="">@lang('admin/hubs.host_DEVICES') ({{ count($item->devices) }}):</label>
        <div class="form-control" style="height: auto;">
        @forelse($item->devices as $v)
        <div>[{{ $v->channel }}] {{ $v->name }}</div>
        @empty
        -//-
        @endforelse
        </div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="hostDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        //
    });

    function hostDelete() {
        confirmYesNo("@lang('admin/hubs.host_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.hub-host-delete", $item->id) }}',
                data: {_token: '{{ csrf_token() }}'},
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            window.location.reload();
                        });
                    } else {

                    }
                },
            });
        });
    }

</script>
@endsection
