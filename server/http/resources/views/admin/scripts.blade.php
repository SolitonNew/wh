@extends('admin.admin')

@section('top-menu')
BUTTONS
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 300px;min-width:300px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="scriptsList">
        @foreach(\App\Http\Models\ScriptsModel::orderBy('COMM', 'asc')->get() as $row)
        <a href="{{ route('scripts', $row->ID) }}" 
           class="tree-item {{ $row->ID == $scriptID ? 'active' : '' }}">{{ $row->COMM }}</a>
        @endforeach
    </div>
    <div class="content-body">
        <div style="font-family: 'Courier New'; padding: 1rem;">{!! $sourceCode !!}</div>
    </div>
</div>
@endsection