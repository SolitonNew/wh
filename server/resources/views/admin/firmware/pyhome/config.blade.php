from variables import Variable

# Variables
@foreach($varList as $v)
@if($v->typ != 'ow')
{{ $v->name }} = Variable({{ $v->id }}, {{ $v->hub_id }}, 0, '{{ $v->typ }}', '{{ $v->channel }}')
@else
{{ $v->name }} = Variable({{ $v->id }}, {{ $v->hub_id }}, 0, '{{ $v->typ }}', '{{ $v->channel }}')
@endif
@endforeach

# Scripts
def script_49():
    pass

# Links
LIVING_S.set_change_script(script_1)
