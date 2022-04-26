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
    <table id="forecast_list" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_TIME')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_TEMP')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_P')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_CC')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_G')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_H')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_V')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_WD')</span></th>
                <th scope="col" style="width: 60px;"><span>@lang('admin/jurnal.forecast_WS')</span></th>
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
                <td>{{ $row->G }}</td>
                <td>{{ $row->H }}</td>
                <td>{{ $row->V }}</td>
                <td>{{ $row->WD }}</td>
                <td>{{ $row->WS }}</td>
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
</script>
@endsection