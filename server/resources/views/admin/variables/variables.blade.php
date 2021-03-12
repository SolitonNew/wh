@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="variableAdd(); return false;">@lang('admin/variables.variable_add')</a>
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="variablesRoomList">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
        <a href="{{ route('variables', $row->id) }}"
           class="tree-item {{ $row->id == $partID ? 'active' : '' }}" style="padding-left: {{ $row->level + 1 }}rem">{{ $row->name }}</a>
        @endforeach
    </div>
    <div class="content-body" scroll-store="variablesList">
        <table id="variable_table" class="table table-sm table-hover table-bordered table-fixed-header">
            <thead>
                <tr>
                    <th scope="col" style="width: 60px;"><span>@lang('admin/variables.table_id')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin/variables.table_controller')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_typ')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_readonly')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_name')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_comm')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_app_control')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_value')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_channel')</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr data-id="{{ $row->id }}" class="{{ $row->with_events ? 'row-with-events' : '' }}">
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->controller_name }}</td>
                    <td>{{ $row->typ }}</td>
                    <td>{{ Lang::get('admin/variables.table_readonly_list.'.$row->direction) }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->comm }}</td>
                    <td>{{ lang::get('admin/variables.app_control.'.$row->app_control) }}</td>
                    <td>{{ $row->value }}</td>
                    <td>{{ $row->channel }}</td>
                </tr>
                @empty
                <tr class="table-empty">
                    <td colspan="9">@lang('dialogs.table_empty')</td>
                </tr>                
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    let currentPartID = '{{ $partID }}';

    $(document).ready(() => {
        $('#variable_table tbody tr').on('click', function () {
            if ($(this).hasClass('table-empty')) return ;
            dialog('{{ route("variable-edit", "") }}/' + $(this).data('id'));
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
