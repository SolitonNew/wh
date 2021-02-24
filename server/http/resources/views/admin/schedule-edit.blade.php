@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin/schedule.schedule_add_title')
@else
    @lang('admin/schedule.schedule_edit_title')
@endif
@endsection

@section('content')
<form id="schedule_edit_form" class="container" method="POST" action="{{ route('schedule-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/schedule.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/schedule.table_COMM')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" name="COMM" value="{{ $item->COMM }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/schedule.table_ACTION')</div>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="ACTION" rows="3" style="font-family: 'Courier New';">{{ $item->ACTION }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/schedule.table_ENABLE')</div>
        </div>
        <div class="col-sm-5">
            <select class="custom-select" name="ENABLE">
            @foreach(Lang::get('admin/schedule.enable_list') as $key => $val)
            <option value="{{ $key }}" {{ $key == $item->ENABLE ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
        <div style="display: inline-block; text-align: right;flex-grow: 1;padding-right: 1rem;">
            <a href="#" class="btn btn-warning nowrap" onclick="runTest()">@lang('admin/schedule.btn_test')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/schedule.table_INTERVAL_TYPE')</div>
        </div>
        <div class="col-sm-5">
            <select class="custom-select" name="INTERVAL_TYPE">
            @foreach(Lang::get('admin/schedule.interval') as $key => $val)
            <option value="{{ $key }}" {{ $key == $item->INTERVAL_TYPE ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="day_of_type">
        <div class="col-sm-3">
            <div class="form-label strong nowrap">@lang('admin/schedule.table_INTERVAL_DAY_OF_TYPE')</div>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="INTERVAL_DAY_OF_TYPE">{{ $item->INTERVAL_DAY_OF_TYPE }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong nowrap">@lang('admin/schedule.table_INTERVAL_TIME_OF_DAY')</div>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="INTERVAL_TIME_OF_DAY">{{ $item->INTERVAL_TIME_OF_DAY }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0 && Auth::user()->ID != $item->ID)
    <button type="button" class="btn btn-danger" onclick="scheduleDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="scheduleEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#schedule_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                console.log(data);
                dialogShowErrors(data);
            }
        });

        $('#schedule_edit_form select[name="INTERVAL_TYPE"]').on('change', function () {
            let row = $('#day_of_type');
            let label = $('.form-label', row);
            switch ($(this).val()) {
                case '0':
                    row.hide(250);
                    break;
                case '1':
                    label.text('@lang("admin/schedule.day_of_types.1")');
                    row.show(250);
                    break;
                case '2':
                    label.text('@lang("admin/schedule.day_of_types.2")');
                    row.show(250);
                    break;
                case '3':
                    label.text('@lang("admin/schedule.day_of_types.3")');
                    row.show(250);
                    break;
            }
        }).trigger('change');
    });

    function scheduleEditOK() {
        $('#schedule_edit_form').submit();
    }

    function scheduleDelete() {
        confirm("@lang('dialogs.confirm_title')",
                "@lang('admin/schedule.schedule-delete-confirm')",
                "@lang('dialogs.btn_yes')",
                "@lang('dialogs.btn_no')", () => {
            $.ajax('{{ route("schedule-delete", $item->ID) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {

                }
            });
        });
    }

    function runTest() {
        let s = $('textarea[name="ACTION"]').val();
        alert(s);
    }

</script>
@endsection
