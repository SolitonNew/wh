@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/users.user_add_title')
@else
    @lang('admin/users.user_edit_title')
@endif
@endsection

@section('content')
<form id="user_edit_form" class="container" method="POST" action="{{ route('admin.user-edit', ['id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/users.table_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/users.table_LOGIN')</label>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="login" value="{{ $item->login }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label {{ $item->id > 0 ?: 'strong' }}">@lang('admin/users.table_PASSWORD')</label>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="password">
            <div class="invalid-feedback"></div>
        </div>
        @if($item->id > 0)
        <div class="offset-sm-3 col-sm-9">
            <div class="alert alert-warning" style="margin-top: 0.5rem; margin-bottom: 0;font-size: 90%">
                @lang('admin/users.password_info')
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/users.table_EMAIL')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="email" value="{{ $item->email }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/users.table_ACCESS')</label>
        </div>
        <div class="col-sm-6">
            <select class="custom-select" name="access" {{ $item->id == Auth::user()->id ? 'disabled' : '' }} >
            @foreach($tableAccessList as $key => $val)
                <option value="{{ $key }}" {{ $item->access == $key ? 'selected' : '' }} >{{ $val }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0 && Auth::user()->id != $item->id)
    <button type="button" class="btn btn-danger" onclick="userDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="userEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#user_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });

    function userEditOK() {
        $('#user_edit_form').submit();
    }

    function userDelete() {
        confirmYesNo("@lang('admin/users.user-delete-confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.user-delete", ["id" => $item->id]) }}',
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
