@extends('dialog')

@section('title')
@lang('admin/plan.import_title')
@endsection

@section('content')
<form id="import_form" class="container" method="POST" action="{{ route('admin.plan-import') }}">
    <div class="form-group" style="margin-top: 1rem;">
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="file" name="file">
            <label class="custom-file-label" for="file" data-browse="@lang('admin/plan.import_btn')"></label>
        </div>
        <div class="invalid-feedback" data-formfield="file"></div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-primary" onclick="planImportOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#import_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    reloadWithWaiter();
                });
            } else {
                dialogShowErrors(data);
            }
        });
        $('#file').on('change', function () {
            if (this.files.length) {
                $(this).next().text(this.files[0].name);
            } else {
                $(this).next().text("@lang('admin/plan.import_select')");
            }
        }).trigger('change');
    });

    function planImportOK() {
        $('#import_form').submit();
    }
</script>
@endsection
