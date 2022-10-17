from variables import Variable
import commands

# Variables
@foreach($varList as $v)
@if($v->typ == 'ow')
{{ $v->name }} = Variable({{ $v->id }}, {{ $v->hub_id }}, 1, [{{ $v->rom }}], '{{ $v->channel }}')
@elseif($v->typ == 'pyhome')
{{ $v->name }} = Variable({{ $v->id }}, {{ $v->hub_id }}, 1, 'pyb', '{{ $v->channel }}')
@else
{{ $v->name }} = Variable({{ $v->id }}, {{ $v->hub_id }}, 1, '{{ $v->typ }}', '{{ $v->channel }}')
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
