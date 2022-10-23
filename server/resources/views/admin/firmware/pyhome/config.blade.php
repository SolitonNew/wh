from devices import Device
import commands

# Devices
@foreach($varList as $v)
@if($v->typ == 'ow')
{{ $v->name }} = Device({{ $v->id }}, {{ $v->hub_rom }}, [{{ $v->rom }}], '{{ $v->channel }}')
@elseif($v->typ == 'pyhome')
{{ $v->name }} = Device({{ $v->id }}, {{ $v->hub_rom }}, 'pyb', '{{ $v->channel }}')
@else
{{ $v->name }} = Device({{ $v->id }}, {{ $v->hub_rom }}, '{{ $v->typ }}', '{{ $v->channel }}')
@endif
@endforeach

# Scripts
@foreach($scriptList as $script)
def script_{{ $script->id }}():
{!! $script->data_to_py !!}

@endforeach

# Links
@foreach($eventList as $event)
{{ $event->deviceName }}.set_change_script(script_{{ $event->script_id }})
@endforeach
