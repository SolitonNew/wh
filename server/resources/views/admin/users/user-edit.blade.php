@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin/users.user_add_title')
@else
    @lang('admin/users.user_edit_title')
@endif
@endsection

@section('content')
<form id="user_edit_form" class="container" method="POST" action="{{ route('user-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/users.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/users.table_LOGIN')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="LOGIN" value="{{ $item->LOGIN }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label {{ $item->ID > 0 ?: 'strong' }}">@lang('admin/users.table_PASSWORD')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="password">
            <div class="invalid-feedback"></div>
        </div>
        @if($item->ID > 0)
        <div class="offset-sm-3 col-sm-9">
            <div class="alert alert-warning" style="margin-top: 0.5rem; margin-bottom: 0;font-size: 90%">
                @lang('admin/users.password_info')
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/users.table_EMAIL')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="EMAIL" value="{{ $item->EMAIL }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/users.table_ACCESS')</div>
        </div>
        <div class="col-sm-6">
            <select class="custom-select" name="ACCESS" {{ $item->ID == Auth::user()->ID ? 'disabled' : '' }} >
            @foreach(Lang::get('admin/users.table_access_list') as $key => $val)
                <option value="{{ $key }}" {{ $item->ACCESS == $key ? 'selected' : '' }} >{{ $val }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0 && Auth::user()->ID != $item->ID)
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
            $.ajax('{{ route("user-delete", $item->ID) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {

                }
            });
        });
    }

</script>
@endsection
