@extends('dialog')

@section('title')
@lang('admin/scripts.script_events_title')
@endsection

@section('content')
<form id="script_events_form" class="container" method="POST" action="{{ route('script-events', $id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <select class="custom-select" name="variables[]" multiple="true" style="height: 400px;">
    @foreach(\App\Http\Models\VariablesModel::orderBy('NAME', 'asc')->get() as $row)
    <option value="{{ $row->id }}" {{ in_array($row->id, $data) ? 'selected' : '' }}>{{ $row->name }}</option>
    @endforeach
    </select>
    <div style="padding-top: 1rem;">
        <span>@lang('admin/scripts.variable_count'):</span>
        <span id="variable_count" class="strong"></span>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-primary" onclick="scriptEventsOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#script_events_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });

        $('#script_events_form select').on('change', function() {
            $('#variable_count').text($(this).val().length);
        }).trigger('change');
    });

    function scriptEventsOK() {
        $('#script_events_form').submit();
    }
</script>
@endsection
