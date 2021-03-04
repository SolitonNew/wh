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
            <input type="date" class="form-control" style="width: auto;" name="DATE" value="{{ Session::get('STATISTICS-TABLE-DATE') }}" required="true">
            <span>@lang('admin/statistics.page-table-sql-filtr'):</span>
            <div>
                <input type="text" class="form-control {{ $errors->first('SQL') ? 'is-invalid' : '' }}" 
                       style="width: auto;" name="SQL" value="{{ Session::get('STATISTICS-TABLE-SQL') }}">
            </div>
            <button class="btn btn-primary">@lang('admin/statistics.page-table-show')</button>
        </form>
    </div>
    <div style="flex-grow: 1; overflow: hidden;">
        <div style="position:relative; display: flex; flex-direction: row; height: 100%;">
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
            <div class="content-body" scroll-store="statisticsTabVarValues">
                <table id="statisticsVarList" class="table table-sm table-hover table-bordered table-fixed-header">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 100px;">
                                <span>
                                    <span>@lang('admin/statistics.table_ID')</span>
                                    <span class="text-primary">({{ count($data) }})</span>    
                                </span>
                            </th>
                            <th scope="col" style="width: 180px;"><span>@lang('admin/statistics.table_CHANGE_DATE')</span></th>
                            <th scope="col" style="width: 100px;"><span>@lang('admin/statistics.table_VALUE')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                        <tr data-id="{{ $row->ID }}">
                            <td>{{ $row->ID }}</td>
                            <td>{{ $row->CHANGE_DATE }}</td>
                            <td>{{ $row->VALUE }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($data))
            <div class="statistics-table-right">
                <div class="statistics-table-chart">
                    <canvas id="statisticsTableChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/Chart.min.js"></script>
<script type="text/javascript" src="/js/Chart.bundle.min.js"></script>
<script>
    $(document).ready(() => {
        $('#varFiltr').val(getCookie('statisticsVarFiltr'));
        
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
            
            setCookie('statisticsVarFiltr', $(this).val());
            
        }).trigger('input');
        
        $('#statisticsVarList tbody tr').on('click', function () {
            dialog('{{ route("statistics-table-value-view", "") }}/' + $(this).data('id'));
        });        
        
        @if(count($data))
        initStatisticsTableChart();
        @endif
    });
    
    function initStatisticsTableChart() {
        var ctx = document.getElementById('statisticsTableChart');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    data: [
                    @foreach($data as $row)
                    {x: '{{ $row->CHANGE_DATE }}', y: {{ $row->VALUE }} },
                    @endforeach
                    ],
                    lineTension: 0,
                }]
            },
            options: {
                legend: {display: false},
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm',
                            }
                        },
                        position: 'bottom',
                    }],
                    yAxes: [{
                        ticks: {
                            stepSize: 1.0,
                        }
                    }]
                },
                tooltips: {
                    enabled: false,
                }
            }
        });
    }
    
</script>
@endsection