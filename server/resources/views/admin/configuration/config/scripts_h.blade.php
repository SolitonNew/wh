@foreach($scriptList as $row)
void script_{{ $row->ID }}(void) {
{!! $row->DATA_TO_C !!}
}

@endforeach