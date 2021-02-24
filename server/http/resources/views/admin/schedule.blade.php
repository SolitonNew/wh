@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scheduleAdd(); return false;">@lang('admin/schedule.schedule_add')</a>
@endsection

@section('content')
<div class="content-body" scroll-store="scheduleList">
    <table id="scheduleList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 40px;"><span>@lang('admin/schedule.table_ID')</span></th>
                <th scope="col" style="width: 200px;"><span>@lang('admin/schedule.table_COMM')</span></th>
                <th scope="col" style="width: 90px; max-width: 90px;"><span>@lang('admin/schedule.table_ACTION_DATETIME')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_ACTION')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_INTERVAL')</span></th>
                <th scope="col" style="width: 105px;"><span>@lang('admin/schedule.table_ENABLE')</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr data-id="{{ $row->ID }}">
                <td>{{ $row->ID }}</td>
                <td>{{ $row->COMM }}</td>
                <td>
                    @if($row->ACTION_DATETIME)
                    {{ \Carbon\Carbon::parse($row->ACTION_DATETIME)->format('Y-m-d') }}<br>
                    {{ \Carbon\Carbon::parse($row->ACTION_DATETIME)->format('H:i:s') }}
                    @else
                    @lang('admin/schedule.action_datetime_calc')
                    @endif
                </td>
                <td>{!! nl2br($row->ACTION) !!}</td>
                <td>{!! $row->INTERVAL_TEXT !!}</td>
                <td>{{ Lang::get('admin/schedule.enable_list.'.$row->ENABLE) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(() => {
        $('#scheduleList tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            dialog('{{ route("schedule-edit", "") }}/' + id);
        });
    });

    function scheduleAdd() {
        dialog('{{ route("schedule-edit", "-1") }}');
    }

</script>

@endsection
