@foreach($data as $row)
<div class="demon-log-line" data-id="{{ $row->ID }}">{!! $row->DATA !!}</div>@endforeach