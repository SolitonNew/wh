@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/plan.plan_add_title')
@else
    @lang('admin/plan.plan_edit_title')
@endif
@endsection

@section('content')
<form id="plan_edit_form" class="container" method="POST" action="{{ route('admin.plan-edit', $item->id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_PARENT_ID')</div>
        </div>
        <div class="col-sm-9">
            <select class="custom-select select-tree" name="parent_id">
            <option value="">-//-</option>
            @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
            <option value="{{ $row->id }}"
                {{ $row->id == $item->parent_id ? 'selected' : '' }}
                {{ App\Http\Models\PlanPartsModel::checkIdAsChildOfParentID($row->id, $item->id) ? '' : 'disabled' }}
                >{!! $row->treePath !!} {{ $row->name }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/plan.table_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" style="margin-bottom: 0;">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_BOUNDS_XY')</div>
        </div>
        <div class="col-sm-9">
            <div class="row">
                <div class="col-sm-2">
                    <div class="form-label strong">@lang('admin/plan.table_X')</div>
                </div>
                <div class="col-sm-4">
                    <input class="form-control" type="number" name="X" step="0.01" value="{{ $itemBounds->X }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-2">
                    <div class="form-label strong">@lang('admin/plan.table_Y')</div>
                </div>
                <div class="col-sm-4">
                    <input class="form-control" type="number" name="Y" step="0.01" value="{{ $itemBounds->Y }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_BOUNDS_WH')</div>
        </div>
        <div class="col-sm-9">
            <div class="row" style="margin-bottom: 0;">
                <div class="col-sm-2">
                    <div class="form-label strong">@lang('admin/plan.table_W')</div>
                </div>
                <div class="col-sm-4">
                    <input class="form-control" type="number" name="W" step="0.01" value="{{ $itemBounds->W }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-sm-2">
                    <div class="form-label strong">@lang('admin/plan.table_H')</div>
                </div>
                <div class="col-sm-4">
                    <input class="form-control" type="number" name="H" step="0.01" value="{{ $itemBounds->H }}" required="">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_STYLE_PEN')</div>
        </div>
        <div class="col-sm-9" style="display: flex; max-width: 335px; justify-content: space-between;">
            <select class="custom-select" name="pen_style" style="width: 100px;">
            @foreach(['none', 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'insert', 'outset'] as $val)
            <option {{ $itemStyle->pen_style == $val ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
            </select>
            <input type="number" class="form-control" style="width: 70px;" name="pen_width" value="{{ $itemStyle->pen_width }}">
            <input type="text" class="form-control" style="width: 100px;" name="pen_color" value="{{ $itemStyle->pen_color }}">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/plan.table_STYLE_FILL')</div>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" style="width: 100px;" name="fill_color" value="{{ $itemStyle->fill_color }}">
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
    });

    function planEditOK() {
        $('#plan_edit_form').submit();
    }

    function planDelete() {
        confirmYesNo("@lang('admin/plan.plan-delete-confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.plan-delete", $item->id) }}',
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
