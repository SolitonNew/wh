@extends('admin.admin')

@section('content')
<div class="main-content-with-bar">
    <nav class="navbar">
        <button class="btn btn-primary" type="button" onclick="userAdd();">@lang('admin\users.user_add')</button>
    </nav>
    <div class="main-content-with-bar-container">
        <div class="content-body">
            <table id="userList" class="table table-sm table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col" style="width: 50px;">@lang('admin\users.table_id')</th>
                        <th scope="col" style="width: 150px;">@lang('admin\users.table_login')</th>
                        <th scope="col" style="width: 250px;">@lang('admin\users.table_email')</th>
                        <th scope="col" style="width: 100px;">@lang('admin\users.table_access')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr data-id="{{ $row->id }}">
                        <th scope="row">{{ $row->id }}</th>
                        <td>{{ $row->login }}</td>
                        <td>{{ $row->email }}</td>
                        <td>@lang('admin\users.table_access_list.'.$row->access)</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#userList tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            dialog('{{ route("user-edit", "") }}/' + id);
        });
    });
    
    function userAdd() {
        dialog('{{ route("user-edit", "-1") }}');
    }
</script>
@endsection