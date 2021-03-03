@extends('admin.statistics.statistics')

@section('page-down-menu')
@endsection

@section('page-top-menu')
@endsection

@section('page-content')
<div style="display: flex; flex-direction: column; height: 100%;">
    <div class="navbar navbar-page">
        <div style="width: 320px; margin-left: -1rem; padding: 0px 1rem;">
            <input id="varFiltr" type="text" class="form-control" placeholder="@lang('admin/statistics.page-table-var-filtr')" >
        </div>
        <form class="navbar-page-group" method="POST" action="{{ route('statistics-table', $id) }}">
            {{ csrf_field() }}
            <span class="strong">@lang('admin/statistics.page-table-date-filtr'):</span>
            <input type="date" class="form-control" style="width: auto;" name="DATE" value="{{ old('DATE') }}">
            <span class="strong">@lang('admin/statistics.page-table-sql-filtr'):</span>
            <input type="text" class="form-control" style="width: auto;" name="SQL" value="{{ old('SQL') }}">
            <button class="btn btn-primary">@lang('admin/statistics.page-table-show')</button>
        </form>
    </div>
    <div style="flex-grow: 1; overflow: hidden;">
        <div style="display: flex; flex-direction: row; height: 100%;">
            <div class="tree" style="width: 320px; min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" 
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

<script>
    $(document).ready(() => {
        $('#varFiltr').on('input', function () {
            let s = $(this).val().toUpperCase();
            if (s == '') {
                $('.tree a').show();
            } else {
                $('.tree a').each(function () {
                    let a = $(this);
                    if (a.text().toUpperCase().indexOf(s) > -1) {
                        $(this).show();
                    } else {
                        let comm = $('small', this);
                        
                        if ($(comm[0]).text().toUpperCase().indexOf(s) > -1) {
                            $(this).show();
                        } else {
                            if (comm.length > 1) {
                                if ($(comm[1]).text().toUpperCase().indexOf(s) > -1) {
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            } else {
                                $(this).hide();
                            }
                        }
                    }
                });
            }
        }).trigger('input');
    });
</script>
@endsection