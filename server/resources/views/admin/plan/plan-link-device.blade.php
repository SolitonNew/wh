@extends('dialog')

@section('title')
@if($deviceID == -1)
@lang('admin/plan.plan_add_device_title')
@else
@lang('admin/plan.plan_edit_device_title')
@endif
@endsection

@section('content')
<form id="plan_link_device_form" class="container" method="POST" action="{{ route('admin.plan-link-device', [$planID, $deviceID]) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <input type="hidden" name="add_device_id">
    <div class="form-group">
        <label class="">@lang('admin/plan.device_list'):</label>
        <select class="custom-select" name="device">
        @foreach($devices as $row)
        <option value="{{ $row->id }}" {{ $row->id == $deviceID ? 'selected' : '' }} 
                style="{{ $row->inPlan ? 'font-weight: bold;' : '' }}" >{{ $row->label }}</option>
        @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.device_surface')</label>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="surface">
            @foreach(['top', 'left', 'bottom', 'right', 'roof'] as $row)
            <option {{ $position->surface == $row ? 'selected' : '' }}>{{ $row }}</option>
            @endforeach
            </select>
        </div>
        <div class="col-sm-5">
            <div class="form-control" style="margin-bottom: -10rem; height:calc(9rem + 2px);padding: 1rem;">
                <div style="display: inline-block; border: 1px solid #000000; width: 100%; height: 100%;"></div>                
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.device_offset')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="offset" value="{{ $position->offset }}">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.device_cross')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="cross" value="{{ $position->cross }}">
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($deviceID > 0)
    <button type="button" class="btn btn-danger" onclick="planLinkDeviceDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="planLinkDeviceOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#plan_link_device_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });
    
    function planLinkDeviceOK() {
        $('#plan_link_device_form').submit();
    }
    
    function planLinkDeviceDelete() {
        confirmYesNo("@lang('admin/plan.device_unlink_confirm')", () => {
            $.ajax({
                method: 'delete',
                url: '{{ route("admin.plan-unlink-device", $deviceID) }}',
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
