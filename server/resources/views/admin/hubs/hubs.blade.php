@extends('admin.admin')

@section('down-menu')
<style>
    .navbar-down-menu {
        margin-right: 0.5rem;
    }
</style>
<a href="#" class="dropdown-item" onclick="hubAdd(); return false;">@lang('admin/hubs.hub_add')</a>
@if($hubID)
<a href="#" class="dropdown-item" onclick="hubEdit(); return false;">@lang('admin/hubs.hub_edit')</a>
@yield('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="devicesAddAll(); return false;">@lang('admin/hubs.devices_add_all')</a>
@if(\App\Models\Hub::withNetworks($hubID))
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="hubScan(); return false;">@lang('admin/hubs.hub_scan')</a>
@endif
@if(\App\Models\Hub::existsFirmwareHubs())
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="firmware(); return false;">@lang('admin/hubs.firmware')</a>
<a href="#" class="dropdown-item" onclick="hubsReset(); return false;">@lang('admin/hubs.hubs_reset')</a>
@endif
@endif
@endsection

@section('top-menu')
@if($hubID)
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link {{ active_segment(4, 'hosts') }}" 
        href="{{ route('admin.hub-hosts', ['hubID' => $hubID]) }}">@lang('admin/hubs.hosts') ({{ \App\Models\Hub::find($hubID)->hostsCount() }})</a>
    <a class="nav-link {{ active_segment(4, 'devices') }}" 
        href="{{ route('admin.hub-devices', ['hubID' => $hubID]) }}">@lang('admin/hubs.devices') ({{ \App\Models\Hub::find($hubID)->devices->count() }})</a>
</div>
@endif
@yield('page-top-menu')
@endsection

@section('content')
@if($hubID)
<div id="hubsListCompact" class="navbar navbar-page" style="display: none;">
    <select id="hubsListCombobox" class="nav-link custom-select select-tree" style="width: 100%;">
        @foreach(\App\Models\Hub::orderBy('rom', 'asc')->get() as $row)
        <option value="{{ $row->id }}" {{ $row->id == $hubID ? 'selected' : '' }}>{{ $row->name }} [{{ $row->typ }}]</option>
        @endforeach
    </select>
</div>
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div id="hubsList" class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="hubsList">
        @foreach(\App\Models\Hub::orderBy('rom', 'asc')->get() as $row)
        <a href="{{ route('admin.hubs', ['hubID' => $row->id]).'/'.$page }}"
           class="tree-item {{ $row->id == $hubID ? 'active' : '' }}" style="white-space: normal;">
            <div style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; width: 100%;">
                    <div>{{ $row->name }}</div>
                    <div class="text-muted">{{ $row->typ }}</div>
                </div>
                <small class="text-muted">{{ $row->comm }}</small>
            </div>
        </a>
        @endforeach
    </div>
    <div class="content-body">
        @yield('page-content')
    </div>
</div>
@else
<div style="display: flex; flex-direction: column; flex-grow: 1;height: 100%; align-items: center;">
    <div class="page-jumbotron">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/hubs.main_prompt')</h5>
            <a href="javascript:hubAdd()" class="btn btn-primary">@lang('admin/hubs.hub_add')</a>
        </div>
    </div>
</div>
@endif

<script>
    $(document).ready(function () {
        @if($hubID)
        // Compact Navigate
        $('#hubsListCombobox').on('change', function () {
            let a = window.location.href.split('/');
            window.location.href = '{{ route("admin.hubs", ["hubID" => ""]) }}/' + $(this).val() + '/' + a[a.length - 1];
        });
        @endif
    });
    
    function hubAdd() {
        dialog("{{ route('admin.hub-edit', ['id' => -1]) }}");
    }
    
    @if($hubID)
    function devicesAddAll() {
        startGlobalWaiter();
        $.ajax({
            url: '{{ route("admin.hubs-add-devices-for-all-hosts", ["hubID" => $hubID]) }}',
            success: function (data) {
                window.location.reload();
            }
        });
    }
        
    function hubEdit() {
        dialog("{{ route('admin.hub-edit', ['id' => $hubID]) }}");
    }

    function hubScan() {
        dialog("{{ route('admin.hub-network-scan', ['id' => $hubID]) }}", null, function () {
            window.location.reload();
        });
    }
    
    function hubsReset() {
        $.ajax({
            url: '{{ route("admin.hubs-reset") }}',
            success: function (data) {
                if (data != 'OK') {
                    alert(data);
                }
            },
        })
    }
    @endif
    
</script>
@endsection