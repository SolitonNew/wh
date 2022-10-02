@extends('dialog')

@section('title')
@lang('admin/plan.plan_move_childs_title')
@endsection

@section('content')
<form id="plan_move_childs_form" class="container" method="POST" action="{{ route('admin.plan-move-childs', ['id' => $partID]) }}">
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-label strong">@lang('admin/plan.table_DX')</div>
        </div>
        <div class="col-sm-3">
            <input class="form-control" type="numeric" name="DX" value="0" step="0.01" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-label strong">@lang('admin/plan.table_DY')</div>
        </div>
        <div class="col-sm-3">
            <input class="form-control" type="numeric" name="DY" value="0" step="0.01" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-primary" onclick="planMoveChildsEditOK()">@lang('dialogs.btn_ok')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#plan_move_childs_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    reloadWithWaiter();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });

    function planMoveChildsEditOK() {
        $('#plan_move_childs_form').submit();
    }

</script>
@endsection
