@extends('admin.jurnal.jurnal')

@section('page-down-menu')
<a href="#" class="dropdown-item" onclick="forecastClearAll(); return false">@lang('admin/jurnal.forecast_clear_all')</a>
@endsection

@section('page-content')
<style>
    .local-time {
        white-space: nowrap;
        padding: 0.5rem!important;
    }
    
    .end-day {
        border-bottom: 2px solid #0000ff;
    }
</style>
<div class="content-body">
    <table id="forecast_list" class="table table-sm table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_TIME')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_TEMP')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_P')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_CC')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_H')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_V')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_WD')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_WS')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_G')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_MP')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr class="{{ $row->localtime->format('H') == '23' ? 'end-day' : '' }}">
                <th class="local-time">{{ $row->localtime->format('d-m H:i') }}</th>
                <td>{{ $row->TEMP }}</td>
                <td>{{ $row->P }}</td>
                <td>{{ $row->CC }}</td>
                <td>{{ $row->H }}</td>
                <td>{{ $row->V }}</td>
                <td>{{ $row->WD }}</td>
                <td>{{ $row->WS }}</td>
                <td>{{ $row->G }}</td>
                <td>{{ $row->MP }}</td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="10">@lang('dialogs.table_empty')</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function () {
        forecastColors();
    });
    
    function forecastClearAll() {
        confirmYesNo("@lang('admin/jurnal.forecast_clear_all_confirm')", () => {
            startGlobalWaiter();
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.jurnal-forecast-clear") }}',
                data: {
                    _token: '{{ @csrf_token() }}',
                },
                success: function (data) {
                    stopGlobalWaiter();
                    if (data == 'OK') {
                        window.location.reload();
                    } else {
                        console.log(data);
                    }
                },
                error: function (err) {
                    stopGlobalWaiter();
                    console.log(err);
                },
            });
        });
    }
    
    function forecastColors() {
        let rules = [
            {},                                                    // time
            {color: '#ffff00', invert: false, min: 0, max: 28},    // TEMP
            {color: '#00ee00', invert: false, min: 740, max: 750}, // P
            {color: '#aaaaaa', invert: true, min: 0, max: 100},    // CC
            {color: '#00ffff', invert: false, min: 30, max: 100},  // H
            {color: '#333333', invert: true, min: 20, max: 0},     // V
            {color: '#ffffff', invert: false, min: 0, max: 360},   // WD
            {color: '#ff0000', invert: true, min: 3, max: 15},    // WS
            {color: '#ff0000', invert: true, min: 1, max: 20},     // G
            {color: '#0000ff', invert: true, min: 0, max: 1},    // MP
        ];
        
        function colorTD(td, color, invert, minValue, maxValue) {
            let value = td.text();
            
            if (value == '-//-') return ;
            
            let r = parseInt(color.substr(1, 2), 16);
            let g = parseInt(color.substr(3, 2), 16);
            let b = parseInt(color.substr(5, 2), 16);
                        
            let range = maxValue - minValue;
            let level = 1; 
            if (range > 0) {
                level = (parseFloat(value) - minValue) / (maxValue - minValue);
            } else {
                level = (minValue - parseFloat(value)) / (minValue - maxValue);
            }
            if (level < 0) level = 0;
            if (level > 1) level = 1;
            td.css({
                'background-color': 'rgba(' + r + ',' + g + ',' + b + ',' + level + ')',
                'color': invert && level > 0.5 ? '#ffffff' : '#000000',
            });
        }
        
        $('#forecast_list tr').each(function () {
            for (let i = 1; i < rules.length; i++) {
                let r = rules[i];
                colorTD($($(this).children()[i]), r.color, r.invert, r.min, r.max);
            }
        });
    }
</script>
@endsection