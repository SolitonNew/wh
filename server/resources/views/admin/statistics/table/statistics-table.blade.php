@extends('admin.statistics.statistics')

@section('page-down-menu')
@endsection

@section('page-top-menu')
@endsection

@section('page-content')
<div style="display: flex; width: 100%; height: 100%;flex-direction: column;">
    <div class="navbar navbar-page">
        <input type="date" class="form-control" style="width: auto;">
    </div>
    <div style="flex-grow: 1;overflow: hidden;">
        <div style="display: flex; flex-direction: row;height: 100%;">
            <div class="tree" style="width: 320px;min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" 
                 scroll-store="statisticsTabVarList">
                @foreach(\App\Http\Models\VariablesModel::orderBy('NAME')->get() as $row)
                <a href="{{ route('statistics-table', $row->ID) }}"
                    class="tree-item {{ $row->ID == $id ? 'active' : '' }}"
                    style="display: block;">
                    {{ $row->NAME }}
                    <div class="text-muted" style="display: flex;justify-content: space-between;flex-wrap: wrap;margin-right: 0.5rem;">
                        <small class="nowrap">{{ $row->COMM }}</small>
                        @if($row->APP_CONTROL > 0)
                        <small class="nowrap">@lang('admin/variables.app_control.'.$row->APP_CONTROL)</small>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            <div class="content-body">

            </div>
        </div>
    </div>
</div>
@endsection