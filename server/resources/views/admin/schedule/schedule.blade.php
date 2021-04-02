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
                <th scope="col" style="width: 100px; max-width: 90px;"><span>@lang('admin/schedule.table_ACTION_DATETIME')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_ACTION')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_INTERVAL')</span></th>
                <th scope="col" style="width: 105px;"><span>@lang('admin/schedule.table_ENABLE')</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr data-id="{{ $row->id }}" 
                class="{{ (\Carbon\Carbon::parse($row->action_datetime)->gt(now()) && 
                           \Carbon\Carbon::parse($row->action_datetime)->lte(now()->startOfDay()->addDay())) ? 'schedule-exec-today' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->comm }}</td>
                <td>
                    @if($row->action_datetime)
                    {{ \Carbon\Carbon::parse($row->action_datetime)->format('Y-m-d') }}<br>
                    {{ \Carbon\Carbon::parse($row->action_datetime)->format('H:i:s') }}
                    @else
                    @lang('admin/schedule.action_datetime_calc')
                    @endif
                </td>
                <td style="padding: 0;"><div class="scheduleActionViewer" data-source="{!! addslashes($row->action) !!}"></div></td>
                <td>{!! $row->interval_text !!}</td>
                <td>{{ Lang::get('admin/schedule.enable_list.'.$row->enable) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    let scheduleActionOptions = {
    @foreach([\App\Library\Script\ScriptEditor::makeKeywords()] as $row)
        keywords: [
        @foreach($row->keywords as $key => $descr)
            '{{ $key }}',
        @endforeach
        ],
        functions: [
        @foreach($row->functions as $key => $descr)
            {name: '{{ $key }}', description: '{{ $descr }}'},
        @endforeach
        ],
        strings: [
        @foreach($row->strings as $key => $descr)
            {name: '{{ $key }}', description: '{{ $descr }}'},
        @endforeach
        ],
    @endforeach
        readOnly: true,
    };
    
    $(document).ready(() => {
        $('#scheduleList tbody tr').on('click', (e) => {
            let id = $(e.currentTarget).attr('data-id');
            dialog('{{ route("schedule-edit", "") }}/' + id);
        });
        
        $('#scheduleList .scheduleActionViewer').each(function () {
            let viewer = new ScriptEditor(this, scheduleActionOptions);
            viewer.setData(this.getAttribute('data-source'));
        });
    });

    function scheduleAdd() {
        dialog('{{ route("schedule-edit", "-1") }}');
    }

</script>

@endsection
