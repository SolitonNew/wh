@extends('admin.admin')

@section('top-menu')
<div class="strong" style="padding: 0 1rem;">@lang('admin\ow-manager.menu_controller'):</div>
<select id="controller" class="custom-select" style="width: 200px;">
    <option value="">@lang('admin\ow-manager.menu_controller_all')</option>
    @foreach(\App\Http\Models\ControllersModel::orderBy('NAME')->get() as $row)
    <option value="{{ $row->ID }}" {{ $row->ID == $controllerID ? 'selected' : '' }}>{{ $row->NAME }}</option>
    @endforeach
</select>
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="runOwScan(); return false;">@lang('admin\ow-manager.run_ow_scan')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="generateVariablesForOW(); return false;">@lang('admin\ow-manager.generate_ow_vars')</a>
@endsection

@section('content')
<div class="content-body" scroll-store="owManagerList">
    <table id="owList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 50px;"><span>@lang('admin\ow-manager.table_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin\ow-manager.table_CONTROLLER')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin\ow-manager.table_COMM')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin\ow-manager.table_ROM')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin\ow-manager.table_CHANNELS')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin\ow-manager.table_VARIABLES')</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr data-id="{{ $row->ID }}">
                <td>{{ $row->ID }}</td>
                <td>{{ $row->CONTROLLER_NAME }}</td>
                <td>{{ $row->COMM }}</td>
                <td>{{ $row->ROM }}</td>
                <td>{{ $row->CHANNELS }}</td>
                <td>
                    @foreach($row->VARIABLES as $v)
                    <div><a href="#" onclick="showVariable({{ $v->ID }}); return false;">{{ $v->NAME }}</a></div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>
    $(document).ready(() => {
        $('#controller').on('change', function(){
            let id = $(this).val();
            if (id) {
                window.location = '{{ route("ow-manager", "") }}/' + id;
            } else {
                window.location = '{{ route("ow-manager", "") }}';
            }
        });
        
        $('#owList tbody tr').on('click', function (e) {
            if ($(e.target).is('a')) return ;
            showInfo($(this).attr('data-id'));
        });
    });
    
    function showVariable(id) {
        dialog('{{ route("variable-edit", "") }}/' + id);
    }
    
    function runOwScan() {
        alert('RUN OW SCAN');
    }
    
    function generateVariablesForOW() {
        if (confirm('@lang("admin\ow-manager.gen_vars_confirm")')) {
            $.ajax('{{ route("ow-manager-gen-vars") }}').done((data) => {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    alert('ERROR');
                }
            });
        }
    }
    
    function showInfo(id) {
        dialog('{{ route("ow-manager-info", "") }}/' + id);
    }
</script>
@endsection