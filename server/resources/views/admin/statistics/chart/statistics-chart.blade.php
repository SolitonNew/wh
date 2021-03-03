@extends('admin.statistics.statistics')

@section('page-down-menu')
@endsection

@section('page-top-menu')
@endsection

@section('page-content')
<div style="height: 100%; overflow-y: auto;" scroll-store="statisticsChartList">
    @foreach($panels as $panel)
    <div class="statistics-chart-panel" style="height: 250px;">
    </div>
    @endforeach
</div>
@endsection