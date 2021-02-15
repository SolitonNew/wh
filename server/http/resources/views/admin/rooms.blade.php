@extends('admin.admin')

@section('top-menu')
BUTTONS
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
        <a href="{{ route('parts', $row->ID) }}" 
           class="tree-item {{ $row->ID == $partID ? 'active' : '' }}" style="padding-left: {{ $row->level + 1 }}rem">{{ $row->NAME }}</a>
        @endforeach
    </div>
    <div class="content-body">
        
    </div>
</div>
@endsection