@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="userAdd(); return false;">@lang('admin/users.user_add')</a>
@endsection

@section('content')
<div class="content-body">
    <table id="userList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/users.table_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/users.table_LOGIN')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/users.table_EMAIL')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/users.table_ACCESS')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->login }}</td>
                <td>{{ $row->email }}</td>
                <td>@lang('admin/users.table_access_list.'.$row->access)</td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="9">@lang('dialogs.table_empty')</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    $(document).ready(() => {
        $('#userList tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            if (id) {
                dialog('{{ route("admin.user-edit", ["id" => ""]) }}/' + id);
            }
        });
    });

    function userAdd() {
        dialog('{{ route("admin.user-edit", ["id" => -1]) }}');
    }
</script>
@endsection
