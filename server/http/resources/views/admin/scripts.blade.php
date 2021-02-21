@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scriptAdd(); return false;">@lang('admin\scripts.script_add')</a>
@if($data)
<a href="#" class="dropdown-item" onclick="scriptEdit(); return false;">@lang('admin\scripts.script_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="scriptAttachEvent(); return false;">@lang('admin\scripts.script_attach_event')</a>
@endif
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 320px;min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="scriptsList">
        @foreach($list as $row)
        <a href="{{ route('scripts', $row->ID) }}" 
            class="tree-item justify-content-between {{ $row->ID == $scriptID ? 'active' : '' }}"
            style="display: flex; align-items: center;">
            {{ $row->COMM }}
            @if($row->VAR_COUNT > 0)
            <div class="badge badge-pill badge-warning">{{ $row->VAR_COUNT }}</div>
            @endif
        </a>
        @endforeach
    </div>
    <div class="content-body codeedit">
        <div class="numbers">000</div>
        <div class="codetext">{!! $sourceCode !!}</div>
    </div>
</div>

<script>
    $(document).ready(() => {
        let s = $('.content-body .codetext').text();
        let a = s.split('\n');
        let aa = new Array();
        for (let i = 1;  i <= a.length; i++) {
            aa.push(i);
        }
        $('.content-body .numbers').html(aa.join('<br>'));
    });
    
    function scriptAdd() {
        dialog('{{ route("script-edit", -1) }}');
    }
    
    function scriptEdit() {
        dialog('{{ route("script-edit", $scriptID) }}');
    }
    
    function scriptAttachEvent() {
        dialog('{{ route("script-events", $scriptID) }}');
    }
</script>

@endsection