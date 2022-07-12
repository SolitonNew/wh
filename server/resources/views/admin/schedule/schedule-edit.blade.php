@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/schedule.schedule_add_title')
@else
    @lang('admin/schedule.schedule_edit_title')
@endif
@endsection

@section('content')
<form id="schedule_edit_form" class="container" method="POST" action="{{ route('admin.schedule-edit', ['id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/schedule.table_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/schedule.table_COMM')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" name="comm" value="{{ $item->comm }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <label class="strong">@lang('admin/schedule.table_ACTION'):</label>
            <div id="actionEditor" class="border" style="height: 10rem;"></div>
            <div class="invalid-feedback" data-formfield="action"></div>
        </div>
    </div>
    <div class="row mb-0">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/schedule.table_ENABLE')</label>
        </div>
        <div class="col-sm-5 mb-3">
            <select class="custom-select" name="enable">
            @foreach($enableList as $key => $val)
            <option value="{{ $key }}" {{ $key == $item->enable ? 'selected' : '' }}>{{ $val }}</option>
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
            <label class="form-label">@lang('admin/schedule.table_INTERVAL_TYPE')</label>
        </div>
        <div class="col-sm-5">
            <select class="custom-select" name="interval_type">
            @foreach($interval as $key => $val)
            @if($key < 4)
            <option value="{{ $key }}" {{ $key == $item->interval_type ? 'selected' : '' }}>{{ $val }}</option>
            @endif
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row" id="day_of_type">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/schedule.table_INTERVAL_DAY_OF_TYPE')</label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="interval_day_of_type">{{ $item->interval_day_of_type }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/schedule.table_INTERVAL_TIME_OF_DAY')</label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="interval_time_of_day">{{ $item->interval_time_of_day }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="scheduleDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="scheduleEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    var actionEditor = false;
    
    $(document).ready(function () {
        let ctx = document.getElementById('actionEditor');
        let options = {
        @foreach([\App\Library\Script\ScriptEditor::makeKeywords()] as $row)
            keywords: [
            @foreach($row->keywords as $key => $descr)
                '{{ $key }}',
            @endforeach
            ],
            functions: [
            @foreach($row->functions as $key => $descr)
                {name: '{{ $key }}', description: '{{ $descr }}'},
            @endforeach
            ],
            strings: [
            @foreach($row->strings as $key => $descr)
                {name: '{{ $key }}', description: '{{ $descr }}'},
            @endforeach
            ],
        @endforeach
            data: `{!! addslashes($item->action) !!}`,
            readOnly: false,
            name: 'action',
        };
        actionEditor = new ScriptEditor(ctx, options);
        
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

        $('#schedule_edit_form select[name="interval_type"]').on('change', function () {
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
        confirmYesNo("@lang('admin/schedule.schedule-delete-confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.schedule-delete", ["id" => $item->id]) }}',
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

    function runTest() {
        $.post({
            url: '{{ route("admin.script-test") }}',
            data: {
                command: actionEditor.getData(),
            },
            success: function(data) {
                alert(data);
            },
            error: function () {
                alert('ERROR');
            }
        });
    }

</script>
@endsection
