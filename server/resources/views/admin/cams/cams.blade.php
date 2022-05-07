@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="camAdd(); return false;">@lang('admin/cams.cam_add')</a>
@endsection

@section('content')
@if(count($data))
<div class="content-body">
    <table id="cams_list" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/cams.table_ID')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/cams.table_NAME')</span></th>
                <th scope="col" style="width: 800px;"><span>@lang('admin/cams.table_URL')</span></th>
                <th scope="col" style="width: 200px;"><span>@lang('admin/cams.table_ALERT_VAR_ID')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->url }}</td>
                <td><a class="var-link" href="#" data-id="{{ $row->device ? $row->device->id : '' }}">{{ $row->device ? $row->device->name : '' }}</a></td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="9">@lang('dialogs.table_empty')</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@else
<div style="display: flex; flex-direction: column; flex-grow: 1;height: 100%; align-items: center;">
    <div style="min-width: 50%;margin-top: 2rem;">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/cams.main_prompt')</h5>
            <a href="javascript:camAdd()" class="btn btn-primary">@lang('admin/cams.cam_add')</a>
        </div>
    </div>
</div>
@endif

<script>
    $(document).ready(() => {
        $('#cams_list tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            if (id) {
                dialog('{{ route("admin.cam-edit", ["id" => ""]) }}/' + id);
            }
        });

        $('#cams_list a.var-link').on('click', function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            if (id) {
                dialog('{{ route("admin.hub-device-edit", ["hubID" => -1, "id" => ""]) }}/' + id);
            }
            
            return false;
        });
    });

    function camAdd() {
        dialog('{{ route("admin.cam-edit", ["id" => "-1"]) }}');
    }
</script>
@endsection
