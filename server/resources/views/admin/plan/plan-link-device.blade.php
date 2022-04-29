@extends('dialog')

@section('title')
@if($deviceID == -1)
@lang('admin/plan.plan_add_device_title')
@else
@lang('admin/plan.plan_edit_device_title')
@endif
@endsection

@section('content')
<style>
    #plan_link_device_form .roof,
    #plan_link_device_form .not-roof {
        display: none;
    }
</style>

<form id="plan_link_device_form" class="container" method="POST" action="{{ route('admin.plan-link-device', [$planID, $deviceID]) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <input type="hidden" name="add_device_id">
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.device_path')</label>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $planPath }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/plan.device')</label>
        </div>
        <div class="col-sm-9">
            @if($deviceID == -1)
            <select class="custom-select" name="device" data-app-control="0">
            @foreach($devices as $row)
            <option value="{{ $row->id }}" {{ $row->id == $deviceID ? 'selected' : '' }} 
                    data-app-control="{{ $row->app_control }}">{{ $row->label }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
            @else
            <div class="form-control">{{ $device->label }}</div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.device_surface')</label>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="surface">
            @foreach(['top', 'right', 'bottom', 'left', 'roof'] as $row)
            <option {{ $position->surface == $row ? 'selected' : '' }}>{{ $row }}</option>
            @endforeach
            </select>
        </div>
        <div class="col-sm-5">
            <div class="form-control" style="margin-bottom: -10rem; height:calc(9rem + 2px); padding: 1rem; overflow: hidden;">
                <div id="deviceLinkView" 
                     style="position: relative; display: inline-block; overflow: hiddend;
                            border: 1px solid #000000; width: 100%; height: 100%;">
                    <div style="position: absolute; left: 0px; top: 0px; display: flex; width: 100%;height: 100%;align-items: center;justify-content: center;">
                        <small class="text-muted">{{ $partBounds->W }}x{{ $partBounds->H }}</small>
                    </div>
                    <div class="plan-device" style="cursor: default;"></div>
                </div>                
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label not-roof">@lang('admin/plan.device_offset')</label>
            <label class="form-label roof">@lang('admin/plan.device_offset_roof')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="offset" value="{{ $position->offset }}" step="0.01">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label not-roof">@lang('admin/plan.device_cross')</label>
            <label class="form-label roof">@lang('admin/plan.device_cross_roof')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="cross" value="{{ $position->cross }}" step="0.01">
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($deviceID > 0)
    <button type="button" class="btn btn-danger" onclick="planLinkDeviceDelete()">@lang('dialogs.btn_remove')</button>
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
        
        let updateAppControl = function (appControl) {
            let devClass = 'dev-';
            let device = $('#plan_link_device_form .plan-device');
            device.attr('class').split(/\s+/).forEach(function (item) {
                if (item.length <= devClass.length + 2) {
                    if (item.substr(0, devClass.length) == devClass) {
                        device.removeClass(item);
                    }
                }
            });
            device.addClass(devClass + appControl);
        };
        
        @if($deviceID == -1)
        $('#plan_link_device_form select[name="device"]').on('change', function () {
            updateAppControl($('#plan_link_device_form option[value="' + $(this).val() + '"]').data('app-control'));
        }).trigger('change');
        @else
        updateAppControl({{ $device->app_control }});
        @endif
        
        $('#plan_link_device_form select[name="surface"]').on('change', function () {
            planViewUpdate();
        });
        
        $('#plan_link_device_form input[name="offset"]').on('input', function () {
            planViewUpdate();
        });
        
        $('#plan_link_device_form input[name="cross"]').on('input', function () {
            planViewUpdate();
        });
        
        setTimeout(function () {
            planViewUpdate();
        }, 150);
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
                        alert(data.errors);
                    }
                },
            });
        });
    }
    
    function planViewUpdate() {
        let b_w = {{ $partBounds->W }};
        if (b_w == 0) b_w = 1;
        let b_h = {{ $partBounds->H }};
        if (b_h == 0) b_h = 1;
        
        let b_top = false;
        let b_right = false;
        let b_bottom = false;
        let b_left = false;
        
        // Корректируем интерфейс
        if ($('#plan_link_device_form select[name="surface"]').val() == 'roof') {
            $('#plan_link_device_form .not-roof').fadeOut(100, function () {
                $('#plan_link_device_form .roof').fadeIn(100);
            });
        } else {
            $('#plan_link_device_form .roof').fadeOut(100, function () {
                $('#plan_link_device_form .not-roof').fadeIn(100);
            });
        }

        // Применяем стили для стен
        switch ($('#plan_link_device_form select[name="surface"]').val()) {
            case 'top':
                b_top = true;
                break;
            case 'right':
                b_right = true;
                break;
            case 'bottom':
                b_bottom = true;
                break;
            case 'left':
                b_left = true;
                break;
            case 'roof':
                b_top = true;
                b_right = true;
                b_bottom = true;
                b_left = true;
                break;
        }

        $('#deviceLinkView').css({
            'border-top-width': (b_top ? 3 : 1) + 'px',
            'border-right-width': (b_right ? 3 : 1) + 'px',
            'border-bottom-width': (b_bottom ? 3 : 1) + 'px',
            'border-left-width': (b_left ? 3 : 1) + 'px',
        });
        
        // Двигаем устройство
        let device = $('#deviceLinkView .plan-device');
        
        let w = $('#deviceLinkView').width() - 4; /* Учитываем толщину обводки */
        let h = $('#deviceLinkView').height() - 4;
        let kx = (w - device.width()) / b_w;
        let ky = (h - device.height()) / b_h;
        
        let offset = $('#plan_link_device_form input[name="offset"]').val();
        let cross = $('#plan_link_device_form input[name="cross"]').val();
        
        switch ($('#plan_link_device_form select[name="surface"]').val()) {
            case 'top':
                device.css({
                    left: (offset * kx) + 'px',
                    top: '0px',
                });
                break;
            case 'right':
                device.css({
                    left: (w - device.width()) + 'px',
                    top: (offset * ky) + 'px',
                });
                break;
            case 'bottom':
                device.css({
                    left: (w - offset * kx - device.width()) + 'px',
                    top: (h - device.height()) + 'px',
                });
                break;
            case 'left':
                device.css({
                    left: '0px',
                    top: (h - offset * ky - device.height()) + 'px',
                });
                break;
            case 'roof':
                device.css({
                    left: (offset * kx) + 'px',
                    top: (cross * ky) + 'px',
                });
                break;
        }
    }
</script>
@endsection
