@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="camAdd(); return false;">@lang('admin/cams.cam_add')</a>
@endsection

@section('content')
<div class="content-body">
    <table id="camsList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 50px;"><span>@lang('admin/cams.table_ID')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/cams.table_NAME')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/cams.table_URL')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/cams.table_ALERT_VAR_ID')</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr data-id="{{ $row->ID }}">
                <td>{{ $row->ID }}</td>
                <td>{{ $row->NAME }}</td>
                <td>{{ $row->URL }}</td>
                <td><a class="var-link" href="#" data-id="{{ $row->ALERT_VAR_ID }}">{{ $row->VAR_NAME }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(() => {
        $('#camsList tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            dialog('{{ route("cam-edit", "") }}/' + id);
        });

        $('#camsList a.var-link').on('click', function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            dialog('{{ route("variable-edit", "") }}/' + id);
            
            return false;
        });
    });

    function camAdd() {
        dialog('{{ route("cam-edit", "-1") }}');
    }
</script>
@endsection