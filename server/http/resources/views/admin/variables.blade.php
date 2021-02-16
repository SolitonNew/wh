@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="variableAdd(); return false;">@lang('admin\variables.variable_add')</a>
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="variablesRoomList">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
        <a href="{{ route('variables', $row->ID) }}"
           class="tree-item {{ $row->ID == $partID ? 'active' : '' }}" style="padding-left: {{ $row->level + 1 }}rem">{{ $row->NAME }}</a>
        @endforeach
    </div>
    <div class="content-body" scroll-store="variablesList">
        <table id="variable_table" class="table table-sm table-hover table-bordered table-fixed-header">
            <thead>
                <tr>
                    <th scope="col" style="width: 60px;"><span>@lang('admin\variables.table_id')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin\variables.table_controller')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_typ')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_readonly')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_name')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_comm')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_app_control')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_value')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin\variables.table_channel')</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr data-id="{{ $row->ID }}">
                    <td>{{ $row->ID }}</td>
                    <td>{{ $row->CONTROLLER_NAME }}</td>
                    <td>{{ $row->ROM }}</td>
                    <td>{{ Lang::get('admin\variables.table_readonly_list.'.$row->DIRECTION) }}</td>
                    <td>{{ $row->NAME }}</td>
                    <td>{{ $row->COMM }}</td>
                    <td>{{ Lang::get('admin\variables.app_control.'.$row->APP_CONTROL) }}</td>
                    <td>{{ $row->VALUE }}</td>
                    <td>{{ $row->CHANNEL }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    let currentPartID = '{{ $partID }}';
    
    $(document).ready(() => {
        $('#variable_table tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            dialog('{{ route("variable-edit", "") }}/' + id);
        });
        
        $('.tree a').on('click', (e) => {
            if (!$(e.currentTarget).hasClass('active')) {
                resetScrollStore($('.content-body'));
            }
        });
    });
    
    function variableAdd() {
        dialog('{{ route("variable-edit", -1) }}');
    }
</script>
@endsection