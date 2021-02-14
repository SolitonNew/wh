@extends('admin.admin')

@section('content')
<div class="main-content-with-bar">
    <nav class="navbar">
        <button class="btn btn-primary" type="button" onclick="userAdd();">@lang('admin\users.user_add')</button>
    </nav>
    <div class="main-content-with-bar-container">
        <div class="content-body">
            <table class="table table-sm table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col" style="width: 50px;">@lang('admin\users.table_id')</th>
                        <th scope="col" style="width: 150px;">@lang('admin\users.table_login')</th>
                        <th scope="col" style="width: 250px;">@lang('admin\users.table_email')</th>
                        <th scope="col" style="width: 100px;">@lang('admin\users.table_access')</th>
                        <th scope="col" style="width: 50px;">@lang('admin\users.table_action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr id="row_{{ $row->id }}">
                        <th scope="row">{{ $row->id }}</th>
                        <td>{{ $row->login }}</td>
                        <td>{{ $row->email }}</td>
                        <td>@lang('admin\users.table_access_list.'.$row->access)</td>
                        <td class="nowrap">
                            <a href="#" class="btn btn-sm btn-outline-primary" onclick="userEdit({{ $row->id }}); return false;">@lang('admin\users.user_edit')</a>
                            <a href="#" class="btn btn-sm btn-danger {{ Auth::user()->id == $row->id ? 'disabled' : '' }}" onclick="userDelete({{ $row->id }}); return false;">@lang('admin\users.user_delete')</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function userAdd() {
        dialog('{{ route("user-edit", "-1") }}');
    }
    
    function userEdit(id) {
        dialog('{{ route("user-edit", "") }}/' + id);
    }
    
    function userDelete(id) {
        if (confirm('@lang("admin\users.user-delete-confirm")')) {
            $.ajax('{{ route("user-delete", "") }}/' + id).done((data) => {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    
                }
            });
        }
    }
</script>
@endsection