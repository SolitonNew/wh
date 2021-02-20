@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scriptAdd(); return false;">@lang('admin\scripts.script_add')</a>
<a href="#" class="dropdown-item" onclick="scriptEdit(); return false;">@lang('admin\scripts.script_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="scriptAttacheEvent(); return false;">@lang('admin\scripts.script_attache_event')</a>
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 300px;min-width:300px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="scriptsList">
        @foreach(\App\Http\Models\ScriptsModel::orderBy('COMM', 'asc')->get() as $row)
        <a href="{{ route('scripts', $row->ID) }}" 
           class="tree-item {{ $row->ID == $scriptID ? 'active' : '' }}">{{ $row->COMM }}</a>
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
        alert('ADD SCRIPT');
    }
    
    function scriptEdit() {
        alert('EDIT SCRIPT');
    }
    
    function scriptAttacheEvent() {
        alert('ATTACHE EVENT');
    }
</script>

@endsection