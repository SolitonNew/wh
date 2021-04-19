@extends('terminal.terminal')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li style="flex-grow: 1;">@lang('terminal.menu_home')</li>
        <li><a href="{{ route('terminal.checked') }}">@lang('terminal.menu_checked')</a></li>
    </ol>
</nav>

<div style="display: flex; flex-direction: column; min-height: calc(100vh - 6rem);">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-{{ $columnCount }}" style="flex-grow: 1;">
    @foreach($data as $group)
    <div class="col">
        <div class="list-group list-group-flush main-column" style="margin-bottom: 1rem;">
            <div class="alert alert-light" role="alert" style="margin-bottom: 0px;">
                {{ $group->title }}
            </div>
            @if(isset($group->rooms) && is_array($group->rooms))
            @foreach($group->rooms as $room)
            <div class="list-group-item main-item">
                <a href="{{ route('terminal.room', [$room->id]) }}">{{ $room->titleCrop }}</a>
                @if($room->temperature_id > -1)
                <div id="variable_{{ $room->temperature_id }}" class="main-item-value" app_control="1">
                    <span class="main-item-value-text">{{ $room->temperature_val }}</span><span class="main-item-value-label">°C</span>
                </div>
                @endif
                @if($room->switch_1_id > -1)
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" app_control="2"
                           id="variable_{{ $room->switch_1_id }}" {{ $room->switch_1_val > 0 ? 'checked=""' : '' }}>
                    <label class="custom-control-label" for="variable_{{ $room->switch_1_id }}"></label>
                </div>
                @endif
                @if($room->switch_2_id > -1)
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" app_control="2"
                           id="variable_{{ $room->switch_2_id }}" {{ $room->switch_2_val > 0 ? 'checked=""' : '' }}>
                    <label class="custom-control-label" for="variable_{{ $room->switch_2_id }}"></label>
                </div> 
                @endif
            </div>
            @endforeach
            @endif
        </div>
    </div>
    @endforeach
    </div>
    @include('terminal.video-list')
</div>

<script>
    function variableOnChanged(varID, varValue, varTime) {
        var v = $('#variable_' + varID);
        switch (v.attr('app_control')) {
            case '1':
                $('.main-item-value-text', v).text(varValue);
                break;
            case '2':
                v.prop('checked', parseInt(varValue) > 0);
                break;
        }
    }
</script>

@endsection