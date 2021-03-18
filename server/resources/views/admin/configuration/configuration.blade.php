@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="configurationAdd(); return false;">@lang('admin/configuration.controller_add')</a>
@if($id)
<a href="#" class="dropdown-item" onclick="configurationEdit(); return false;">@lang('admin/configuration.controller_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="runOwScan(); return false;">@lang('admin/configuration.controller_ow_scan')</a>
<a href="#" class="dropdown-item" onclick="generateVariablesForOW(); return false;">@lang('admin/configuration.generate_ow_vars')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="configurationApply(); return false;">@lang('admin/configuration.configuration_apply')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onClick="configurationReset(); return false;">@lang('admin/configuration.configuration-reset')</a>
@endif
@endsection

@section('top-menu')

@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="configurationContrList">
        @foreach(\App\Http\Models\ControllersModel::orderBy('id', 'asc')->get() as $row)
        <a href="{{ route('configuration', $row->id) }}"
            class="tree-item {{ $row->id == $id ? 'active' : '' }}">
            <div>
                <div class="{{ $row->is_server ? 'strong' : '' }}" >{{ $row->name }}</div>
                <small class="text-muted">{{ $row->comm }}</small>
            </div>
        </a>
        @endforeach
    </div>
    <div class="content-body" scroll-store="configurationOWList">
        <table id="owList" class="table table-sm table-hover table-bordered table-fixed-header">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/configuration.table_ID')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin/configuration.table_COMM')</span></th>
                    <th scope="col" style="width: 300px;"><span>@lang('admin/configuration.table_ROM')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin/configuration.table_CHANNELS')</span></th>
                    <th scope="col" style="width: 250px;"><span>@lang('admin/configuration.table_VARIABLES')</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr data-id="{{ $row->id }}">
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->comm }}</td>
                    <td class="nowrap">{{ $row->rom }}</td>
                    <td>{{ $row->channels }}</td>
                    <td>
                        @foreach($row->variables as $v)
                        <div><a class="nowrap" href="#" onclick="showVariable({{ $v->id }}); return false;">[{{ $v->channel }}] {{ $v->name }}</a></div>
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
</div>

<script>
    $(document).ready(() => {
        $('#owList tbody tr').on('click', function (e) {
            if ($(this).hasClass('table-empty')) return ;
            if ($(e.target).is('a')) return ;
            dialog('{{ route("configuration-ow-info", "") }}/' + $(this).data('id'));
        });
    });
    
    function configurationAdd() {
        dialog('{{ route("configuration-edit", -1) }}');
    }
    
    @if($id)
    function configurationEdit() {
        dialog('{{ route("configuration-edit", $id) }}');
    }
    @endif

    function showVariable(id) {
        dialog('{{ route("variable-edit", "") }}/' + id);
    }

    function runOwScan() {
        alert('RUN OW SCAN');
    }

    function generateVariablesForOW() {
        confirmYesNo('@lang("admin/configuration.gen_vars_confirm")', () => {
            $.ajax('{{ route("configuration-gen-vars") }}').done((data) => {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    alert('ERROR');
                }
            });
        });
    }
    
    function configurationApply() {
        $.ajax({
            url: '{{ route("configuration-apply", "") }}',
            success: function (data) {
                if (data != 'OK') {
                    alert(data);
                }
            },
        })
    }
    
    function configurationReset() {
        $.ajax({
            url: '{{ route("configuration-reset") }}',
            success: function (data) {
                if (data != 'OK') {
                    alert(data);
                }
            },
        })
    }
    
</script>
@endsection