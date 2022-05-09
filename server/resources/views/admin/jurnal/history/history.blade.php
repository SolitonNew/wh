@extends('admin.jurnal.jurnal')

@section('page-down-menu')
<a href="#" class="dropdown-item" onclick="jurnalHistoryDeleteAllVisible(); return false;">@lang('admin/jurnal.history_delete_all_visible')</a>
@endsection

@section('page-content')
<div style="display: flex; flex-direction: column; height: 100%;">
    <div class="navbar navbar-page">
        <div id="jurnalHistoryFiltrPanel" style="width: 320px; margin-left: -1rem; padding: 0px 1rem;">
            <input id="jurnalHistoryFiltr" type="text" class="form-control" placeholder="@lang('admin/jurnal.history_var_filtr')" >
        </div>
        <form id="historyFilter" class="navbar-page-group" method="POST" action="{{ route('admin.jurnal-history', ['id' => $id]) }}">
            <span class="strong">@lang('admin/jurnal.history_date_filtr'):</span>
            <input type="date" class="form-control" style="width: auto;" name="date" value="{{ isset($_COOKIE['STATISTICS-TABLE-DATE']) ? $_COOKIE['STATISTICS-TABLE-DATE'] : '' }}" required="true">
            <span>@lang('admin/jurnal.history_sql_filtr'):</span>
            <div>
                <input type="text" class="form-control {{ $errors->first('sql') ? 'is-invalid' : '' }}" 
                       style="width: auto;" name="sql" value="{{ isset($_COOKIE['STATISTICS-TABLE-SQL']) ? $_COOKIE['STATISTICS-TABLE-SQL'] : '' }}">
            </div>
            <button id="jurnalHistoryBtn" class="btn btn-primary" style="display:none;">@lang('admin/jurnal.history_show')</button>
        </form>
    </div>
    <div style="flex-grow: 1; overflow: hidden;">
        <div style="position:relative; display: flex; flex-direction: row; height: 100%;">
            <div id="historyList" class="tree" style="width: 320px; min-width:320px; border-right: 1px solid rgba(0,0,0,0.125);" 
                 scroll-store="jurnalHistoryVarList">
                @foreach($devices as $row)
                <a href="{{ route('admin.jurnal-history', ['id' => $row->id]) }}"
                    class="tree-item {{ $row->id == $id ? 'active' : '' }}"
                    style="display: block; justify-content: space-between; white-space: normal;">
                    {{ $row->name }}
                    <div class="text-muted" style="display: flex;justify-content: space-between;flex-wrap: wrap;margin-right: 0.5rem;">
                        <small class="nowrap">{{ $row->comm ?? ($row->room ? $row->room->name : '') }}</small>
                        @if($row->app_control > 0)
                        <small class="nowrap">@lang('admin/hubs.app_control.'.$row->app_control)</small>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            <div id="historyBody" class="content-body" scroll-store="statisticsTabVarValues">
                <table id="jurnal_history_List" class="table table-sm table-hover table-bordered table-fixed-header">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 100px;">
                                <span>
                                    <span>@lang('admin/jurnal.history_ID')</span>
                                    <span class="text-primary">({{ count($data) }})</span>    
                                </span>
                            </th>
                            <th scope="col" style="width: 180px;"><span>@lang('admin/jurnal.history_CREATED_AT')</span></th>
                            <th scope="col" style="width: 100px;"><span>@lang('admin/jurnal.history_VALUE')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                        <tr data-id="{{ $row->id }}">
                            <td>{{ $row->id }}</td>
                            <td>{{ parse_datetime($row->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $row->value }}</td>
                        </tr>
                        @empty
                        <tr class="table-empty">
                            <td colspan="3">@lang('dialogs.table_empty')</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(count($data))
            <div class="statistics-table-right">
                <div class="statistics-table-chart">
                    <canvas id="jurnalHistoryChart" style="width: 100%; height: 100%;"></canvas>
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
        $('#jurnalHistoryFiltr').val(getCookie('jurnalHistoryFiltr'));
        
        $('#historyFilter').on('submit', function () {
            setCookie('STATISTICS-TABLE-DATE', $('#historyFilter [name="date"]').val());
            setCookie('STATISTICS-TABLE-SQL', $('#historyFilter [name="sql"]').val());
        });
        
        $('#jurnalHistoryFiltr').on('input', function () {
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
            
            setCookie('jurnalHistoryFiltr', $(this).val());
            
        }).trigger('input');
        
        $('#jurnal_history_List tbody tr').on('click', function () {
            if ($(this).hasClass('table-empty')) return ;
            dialog('{{ route("admin.jurnal-history-value-view", ["id" => ""]) }}/' + $(this).data('id'));
        });
        
        $('input[name="date"], input[name="sql"]').on('input', () => {
            $('#jurnalHistoryBtn').fadeIn(250);
        });
        
        @if(count($data))
        initJurnalHistoryChart();
        @endif
    });
    
    function initJurnalHistoryChart() {
        var ctx = document.getElementById('jurnalHistoryChart');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    data: [
                    @foreach($data as $row)
                    {x: '{{ parse_datetime($row->created_at) }}', y: {{ $row->value }} },
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
    
    @if($id)
    function jurnalHistoryDeleteAllVisible() {
        confirmYesNo("@lang('admin/jurnal.history_delete_all_visible_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: "{{ route('admin.jurnal-history-delete-all-visible', ['id' => $id]) }}",
                data: {
                    
                },
                success: function (data) {
                    alert(data);
                    window.location.reload();
                },
            });
        });
    }
    @endif
    
</script>
@endsection