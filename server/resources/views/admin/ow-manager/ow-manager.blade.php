@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="runOwScan(); return false;">@lang('admin/ow-manager.run_ow_scan')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="generateVariablesForOW(); return false;">@lang('admin/ow-manager.generate_ow_vars')</a>
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link {{ !$controllerID ? 'active' : '' }}" href="{{ route('ow-manager', "") }}">@lang('admin/ow-manager.menu_controller_all')</a>
    @foreach(\App\Http\Models\ControllersModel::orderBy('NAME')->get() as $row)
    <a class="nav-link {{ $row->ID == $controllerID ? 'active' : '' }}" href="{{ route('ow-manager', $row->ID) }}">{{ $row->NAME }}</a>
    @endforeach
</div>
@endsection

@section('content')
<div class="content-body" scroll-store="owManagerList">
    <table id="owList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 50px;"><span>@lang('admin/ow-manager.table_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/ow-manager.table_CONTROLLER')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/ow-manager.table_COMM')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/ow-manager.table_ROM')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/ow-manager.table_CHANNELS')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/ow-manager.table_VARIABLES')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->ID }}">
                <td>{{ $row->ID }}</td>
                <td>{{ $row->CONTROLLER_NAME }}</td>
                <td>{{ $row->COMM }}</td>
                <td class="nowrap">{{ $row->ROM }}</td>
                <td>{{ $row->CHANNELS }}</td>
                <td>
                    @foreach($row->VARIABLES as $v)
                    <div><a class="nowrap" href="#" onclick="showVariable({{ $v->ID }}); return false;">[{{ $v->CHANNEL }}] {{ $v->NAME }}</a></div>
                    @endforeach
                </td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="6">@lang('dialogs.table_empty')</td>
            </tr>  
            @endforelse
        </tbody>
    </table>
</div>


<script>
    $(document).ready(() => {
        $('#owList tbody tr').on('click', function (e) {
            if ($(this).hasClass('table-empty')) return ;
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
        confirmYesNo('@lang("admin/ow-manager.gen_vars_confirm")', () => {
            $.ajax('{{ route("ow-manager-gen-vars") }}').done((data) => {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    alert('ERROR');
                }
            });
        });
    }

    function showInfo(id) {
        dialog('{{ route("ow-manager-info", "") }}/' + id);
    }
</script>
@endsection
