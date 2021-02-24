@foreach($data as $row)
<div data-id="{{ $row->ID }}">{!! $row->DATA !!}<br></div>
@endforeach