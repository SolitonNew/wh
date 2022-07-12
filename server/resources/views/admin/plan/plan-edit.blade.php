@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/plan.plan_add_title')
@else
    @lang('admin/plan.plan_edit_title')
@endif
@endsection

@section('content')
<form id="plan_edit_form" class="container" method="POST" action="{{ route('admin.plan-edit', ['id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_PARENT_ID')</label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select select-tree" name="parent_id">
            <option value="">-//-</option>
            @foreach(\App\Models\Room::generateTree() as $row)
            <option value="{{ $row->id }}"
                {{ $row->id == $item->parent_id ? 'selected' : '' }}
                {{ App\Models\Room::checkIdAsChildOfParentID($row->id, $item->id) ? '' : 'disabled' }}
                data-bounds="{{ $row->bounds }}"
                >{!! $row->treePath !!} {{ $row->name }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/plan.table_NAME')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row mb-0">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_BOUNDS_XY')</label>
        </div>
        <div class="col-sm-9">
            <div class="row mb-0">
                <div class="col-sm-2">
                    <label class="form-label strong">@lang('admin/plan.table_X')</label>
                </div>
                <div class="col-sm-4 mb-3">
                    <input class="form-control" type="number" name="X" step="0.01" value="{{ $itemBounds->X }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-2">
                    <label class="form-label strong">@lang('admin/plan.table_Y')</label>
                </div>
                <div class="col-sm-4 mb-3">
                    <input class="form-control" type="number" name="Y" step="0.01" value="{{ $itemBounds->Y }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-0">
        <div class="col-sm-3 mb-0">
            <label class="form-label">@lang('admin/plan.table_BOUNDS_WH')</label>
        </div>
        <div class="col-sm-9">
            <div class="row mb-0">
                <div class="col-sm-2">
                    <label class="form-label strong">@lang('admin/plan.table_W')</label>
                </div>
                <div class="col-sm-4 mb-3">
                    <input class="form-control" type="number" name="W" step="0.01" value="{{ $itemBounds->W }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-2">
                    <label class="form-label strong">@lang('admin/plan.table_H')</label>
                </div>
                <div class="col-sm-4 mb-3">
                    <input class="form-control" type="number" name="H" step="0.01" value="{{ $itemBounds->H }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_STYLE_PEN')</label>
        </div>
        <div class="col-sm-6" style="display: flex;">
            <div class="mr-3" style="flex-grow: 1;">
                <select class="custom-select" name="pen_style">
                @foreach(['none', 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'insert', 'outset'] as $val)
                <option {{ $itemStyle->pen_style == $val ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
                </select>
            </div>
            <div>
                <input type="number" class="form-control" style="width: 70px;" name="pen_width" value="{{ $itemStyle->pen_width }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_STYLE_FILL')</label>
        </div>
        <div class="col-sm-5">
            <select class="custom-select" name="fill">
            @foreach(['background', 'pen', 'diagonal-left', 'diagonal-right', 'cross'] as $row)
            <option value="{{ $row }}" {{ $itemStyle->fill == $row ? 'selected' : '' }}>{{ $row }}</option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/plan.table_NAME_POSITION')</label>
        </div>
        <div class="col-sm-9">
            <div class="row" style="margin-bottom: 0;">
                <div class="col-sm-2">
                    <label class="form-label">@lang('admin/plan.table_NAME_POSITION_DX')</label>
                </div>
                <div class="col-sm-4 mb-3">
                    <input class="form-control" name="name_dx" value="{{ $itemStyle->name_dx }}">
                </div>
                <div class="col-sm-2">
                    <label class="form-label">@lang('admin/plan.table_NAME_POSITION_DY')</label>
                </div>
                <div class="col-sm-4">
                    <input class="form-control" name="name_dy" value="{{ $itemStyle->name_dy }}">
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="planDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="planEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    var planEditFormParentOldBounds = false;
    
    $(document).ready(() => {
        $('#plan_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
        
        planEditFormParentBounds = false;
        
        $('#plan_edit_form select[name="parent_id"]').on('change', function () {
            let val = $(this).val();
            let bounds = $('#plan_edit_form select[name="parent_id"] option[value="' + val + '"]').data('bounds');
            
            if (!bounds) {
                bounds = {
                    X: 0,
                    Y: 0,
                    W: 10,
                    H: 6,
                };
            }
            
            if (planEditFormParentOldBounds) {
                let X = $('#plan_edit_form input[name="X"]');                
                let Xval = X.val() ? parseFloat(X.val()) : 0;
                Xval = Math.floor((Xval + planEditFormParentOldBounds.X - bounds.X) * 100) / 100;
                X.val(Xval);
                
                let Y = $('#plan_edit_form input[name="Y"]');
                let Yval = Y.val() ? parseFloat(Y.val()) : 0;
                Yval = Math.floor((Yval + planEditFormParentOldBounds.Y - bounds.Y) * 100) / 100;
                Y.val(Yval);
            }
            
            planEditFormParentOldBounds = bounds;
        }).trigger('change');
    });

    function planEditOK() {
        $('#plan_edit_form').submit();
    }

    function planDelete() {
        confirmYesNo("@lang('admin/plan.plan-delete-confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.plan-delete", ["id" => $item->id]) }}',
                data: {
                    
                },
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
