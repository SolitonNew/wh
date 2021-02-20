@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin\plan.plan_add_title')
@else
    @lang('admin\plan.plan_edit_title')
@endif
@endsection

@section('content')
<form id="plan_edit_form" class="container" method="POST" action="{{ route('plan-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\plan.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\plan.table_PARENT_ID')</div>
        </div>
        <div class="col-sm-9">
            <select class="custom-select" name="PARENT_ID">
            <option value="">-//-</option>
            @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
            <option value="{{ $row->ID }}" 
                {{ $row->ID == $item->PARENT_ID ? 'selected' : '' }}
                {{ App\Http\Models\PlanPartsModel::checkIdAsChildOfParentID($row->ID, $item->ID) ? '' : 'disabled' }}
                >{!! str_repeat('&nbsp;-&nbsp;', $row->level) !!} {{ $row->NAME }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\plan.table_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="NAME" value="{{ $item->NAME }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" style="margin-bottom: 0;">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\plan.table_BOUNDS_XY')</div>
        </div>
        <div class="col-sm-9">
            <div class="">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-label strong">@lang('admin\plan.table_X')</div>
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" type="number" name="X" step="0.01" value="{{ $itemBounds->X }}" required="">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-label strong">@lang('admin\plan.table_Y')</div>
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" type="number" name="Y" step="0.01" value="{{ $itemBounds->Y }}" required="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\plan.table_BOUNDS_WH')</div>
        </div>
        <div class="col-sm-9">
            <div class="">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-label strong">@lang('admin\plan.table_W')</div>
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" type="number" name="W" step="0.01" value="{{ $itemBounds->W }}" required="">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-label strong">@lang('admin\plan.table_H')</div>
                    </div>
                    <div class="col-sm-4">
                        <input class="form-control" type="number" name="H" step="0.01" value="{{ $itemBounds->H }}" required="">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0)
    <button type="button" class="btn btn-danger" onclick="userDelete()">@lang('dialogs.btn_delete')</button>
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
        if (confirm('@lang("admin\plan.plan-delete-confirm")')) {
            $.ajax('{{ route("plan-delete", $item->ID) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {
                    
                }
            });
        }
    }
    
</script>
@endsection