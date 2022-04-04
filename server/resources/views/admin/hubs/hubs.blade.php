@extends('admin.admin')

@section('down-menu')
<style>
    .navbar-down-menu {
        min-width: 250px;
        margin-right: 0.5rem;
    }
</style>
<a href="#" class="dropdown-item" onclick="hubAdd(); return false;">@lang('admin/hubs.hub_add')</a>
@if($hubID)
<a href="#" class="dropdown-item" onclick="hubEdit(); return false;">@lang('admin/hubs.hub_edit')</a>
@yield('page-down-menu')
@if(\App\Models\Hub::existsFirmwareHubs())
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="hubsScan(); return false;">@lang('admin/hubs.hubs_scan')</a>
<a href="#" class="dropdown-item" onclick="firmware(); return false;">@lang('admin/hubs.firmware')</a>
<a href="#" class="dropdown-item" onclick="hubsReset(); return false;">@lang('admin/hubs.hubs_reset')</a>
@endif
@endif
@endsection

@section('top-menu')
@if($hubID)
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(4, 'hosts')" 
        href="{{ route('admin.hub-hosts', $hubID) }}">@lang('admin/hubs.hosts') ({{ App\Models\OwHost::whereHubId($hubID)->count() }})</a>
    <a class="nav-link @activeSegment(4, 'devices')" 
        href="{{ route('admin.hub-devices', $hubID) }}">@lang('admin/hubs.devices') ({{ App\Models\Device::whereHubId($hubID)->count() }})</a>
</div>
@endif
@yield('page-top-menu')
@endsection

@section('content')
@if($hubID)
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="hubsList">
        @foreach(\App\Models\Hub::orderBy('rom', 'asc')->get() as $row)
        <a href="{{ route('admin.hubs', $row->id).'/'.$page }}"
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
    <div style="min-width: 50%;margin-top: 2rem;">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/hubs.main_prompt')</h5>
            <a href="javascript:hubAdd()" class="btn btn-primary">@lang('admin/hubs.hub_add')</a>
        </div>
    </div>
</div>
@endif

<script>
    $(document).ready(function () {
        
    });
    
    function hubAdd() {
        dialog("{{ route('admin.hub-edit', -1) }}");
    }
    
    @if($hubID)
    function hubEdit() {
        dialog("{{ route('admin.hub-edit', $hubID) }}");
    }

    function hubsScan() {
        dialog("{{ route('admin.hubs-scan') }}", null, function () {
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