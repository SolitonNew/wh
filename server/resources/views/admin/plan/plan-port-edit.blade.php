@extends('dialog')

@section('title')
@if($portIndex == -1)
@lang('admin/plan.plan_add_port_title')
@else
@lang('admin/plan.plan_edit_port_title')
@endif
@endsection

@section('content')
<style>
    .ext-control-column {
        position: absolute;
        top: 1rem;
        right: 20px;
        width: 10rem;
        height: 10rem;
    }

    @media(max-width: 574px) {
        .ext-control-column {
            position: relative;
            display: flex;
            margin-left: calc(50% - 5rem + 20px);
            margin-bottom: 1rem;
        }
    }
</style>
<form id="plan_port_form" class="container" method="POST" action="{{ route('admin.plan-port-edit', ['planID' => $planID, 'portIndex' => $portIndex]) }}">
    <button type="submit" style="display: none;"></button>
    <input type="hidden" name="add_device_id">
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.port_surface')</label>
        </div>
        <div class="col-sm-4">
            <select class="custom-select" name="surface">
            @foreach(['top', 'right', 'bottom', 'left'] as $row)
            <option {{ $position->surface == $row ? 'selected' : '' }}>{{ $row }}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.port_offset')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="offset" value="{{ $position->offset }}" step="0.01">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.port_width')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="width" value="{{ $position->width }}" step="0.01">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.port_depth')</label>
        </div>
        <div class="col-sm-4">
            <input type="number" class="form-control" name="depth" value="{{ $position->depth }}" step="0.01">
        </div>
    </div>
    <div class="ext-control-column">
        <div class="form-control" style="margin-bottom: -10rem; height:calc(9rem + 2px); padding: 1rem; overflow: hidden;">
            <div id="portView"
                 style="position: relative; display: inline-block; overflow: hiddend;
                        border: 1px solid #000000; width: 100%; height: 100%;">
                <div style="position: absolute; left: 0px; top: 0px; display: flex; width: 100%;height: 100%;align-items: center;justify-content: center;">
                    <small class="text-muted">{{ $partBounds->W }}x{{ $partBounds->H }}</small>
                </div>
                <div class="plan-port" style="cursor: default;"></div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($portIndex > -1)
    <button type="button" class="btn btn-danger" onclick="planPortDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="planPortOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#plan_port_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    reloadWithWaiter();
                });
            } else {
                dialogShowErrors(data);
            }
        });

        $('#plan_port_form select[name="surface"]').on('change', function () {
            planViewUpdate();
        });

        $('#plan_port_form input[name="offset"]').on('input', function () {
            planViewUpdate();
        });

        $('#plan_port_form input[name="width"]').on('input', function () {
            planViewUpdate();
        });

        $('#plan_port_form input[name="depth"]').on('input', function () {
            planViewUpdate();
        });

        setTimeout(function () {
            planViewUpdate();
        }, 150);
    });

    function planPortOK() {
        $('#plan_port_form').submit();
    }

    function planPortDelete() {
        confirmYesNo("@lang('admin/plan.port_delete_confirm')", () => {
            $.ajax({
                method: 'delete',
                url: '{{ route("admin.plan-port-delete", ["planID" => $planID, "portIndex" => $portIndex]) }}',
                data: {

                },
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            reloadWithWaiter();
                        });
                    } else {
                        //
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

        // Applying surface styles
        switch ($('#plan_port_form select[name="surface"]').val()) {
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
        }

        $('#portView').css({
            'border-top-width': (b_top ? 3 : 1) + 'px',
            'border-right-width': (b_right ? 3 : 1) + 'px',
            'border-bottom-width': (b_bottom ? 3 : 1) + 'px',
            'border-left-width': (b_left ? 3 : 1) + 'px',
        });

        // Move port
        let port = $('#portView .plan-port');

        let w = $('#portView').width();
        let h = $('#portView').height();
        let kx = w / b_w;
        let ky = h / b_h;
        let offset = $('#plan_port_form input[name="offset"]').val();
        let width = $('#plan_port_form input[name="width"]').val();
        let depth = $('#plan_port_form input[name="depth"]').val();

        let pw = 0;
        let ph = 0;

        switch ($('#plan_port_form select[name="surface"]').val()) {
            case 'top':
                pw = width * kx;
                ph = depth * ky;
                port.css({
                    left: (offset * kx) + 'px',
                    top: (-ph) + 'px',
                    width: pw + 'px',
                    height: ph + 'px',
                });
                break;
            case 'right':
                pw = depth * kx;
                ph = width * ky;
                port.css({
                    left: w + 'px',
                    top: (offset * ky) + 'px',
                    width: pw + 'px',
                    height: ph + 'px',
                });
                break;
            case 'bottom':
                pw = width * kx;
                ph = depth * ky;
                port.css({
                    left: (w - offset * kx - pw) + 'px',
                    top: h + 'px',
                    width: pw + 'px',
                    height: ph + 'px',
                });
                break;
            case 'left':
                pw = depth * kx;
                ph = width * ky;
                port.css({
                    left: -pw + 'px',
                    top: (h - offset * ky - ph) + 'px',
                    width: pw + 'px',
                    height: ph + 'px',
                });
                break;
        }
    }
</script>
@endsection
