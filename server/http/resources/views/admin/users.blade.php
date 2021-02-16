@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="userAdd(); return false;">@lang('admin\users.user_add')</a>
@endsection

@section('content')
<div class="content-body">
    <table id="userList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 50px;"><span>@lang('admin\users.table_id')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin\users.table_login')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin\users.table_email')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin\users.table_access')</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr data-id="{{ $row->id }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->login }}</td>
                <td>{{ $row->email }}</td>
                <td>@lang('admin\users.table_access_list.'.$row->access)</td>
            </tr>
            @endforeach
        </tbody>
    </table>
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