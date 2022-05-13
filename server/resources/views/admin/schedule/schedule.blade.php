@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="scheduleAdd(); return false;">@lang('admin/schedule.schedule_add')</a>
@endsection

@section('content')
@if(count($data))
<div class="content-body" scroll-store="scheduleList">
    <table id="scheduleList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/schedule.table_ID')</span></th>
                <th scope="col" style="width: 200px;"><span>@lang('admin/schedule.table_COMM')</span></th>
                <th scope="col" style="width: 120px; max-width: 90px;"><span>@lang('admin/schedule.table_ACTION_DATETIME')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_ACTION')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/schedule.table_INTERVAL')</span></th>
                <th scope="col" style="width: 105px;"><span>@lang('admin/schedule.table_ENABLE')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}" 
                class="{{ (parse_datetime($row->action_datetime)->gt(\Carbon\Carbon::now()) && 
                           parse_datetime($row->action_datetime)->lte(\Carbon\Carbon::now()->startOfDay()->addDay())) ? 'schedule-exec-today' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->comm }}</td>
                <td>
                    @if($row->action_datetime)
                    {{ parse_datetime($row->action_datetime)->format('Y-m-d') }}<br>
                    {{ parse_datetime($row->action_datetime)->format('H:i:s') }}
                    @else
                    @lang('admin/schedule.action_datetime_calc')
                    @endif
                </td>
                <td style="padding: 0;"><div class="scheduleActionViewer" data-source="{!! addslashes($row->action) !!}"></div></td>
                <td>{!! $row->interval_text !!}</td>
                <td>@lang('admin/schedule.enable_list.'.$row->enable)</td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="9">@lang('dialogs.table_empty')</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@else
<div style="display: flex; flex-direction: column; flex-grow: 1;height: 100%; align-items: center;">
    <div class="page-jumbotron">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/schedule.main_prompt')</h5>
            <a href="javascript:scheduleAdd()" class="btn btn-primary">@lang('admin/schedule.schedule_add')</a>
        </div>
    </div>
</div>
@endif

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
            if (id) {
                dialog('{{ route("admin.schedule-edit", ["id" => ""]) }}/' + id);
            }
        });
        
        $('#scheduleList .scheduleActionViewer').each(function () {
            let viewer = new ScriptEditor(this, scheduleActionOptions);
            let source = this.getAttribute('data-source');
            
            source = source.replace(/\\'/g, '\'');
            source = source.replace(/\\"/g, '"');
            source = source.replace(/\\0/g, '\0');
            source = source.replace(/\\\\/g, '\\');            
            
            viewer.setData(source);
        });
    });

    function scheduleAdd() {
        dialog('{{ route("admin.schedule-edit", ["id" => -1]) }}');
    }

</script>

@endsection
