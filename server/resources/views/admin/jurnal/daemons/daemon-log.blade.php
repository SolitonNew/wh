@foreach($data as $row)
<div class="daemon-log-line" data-id="{{ $row->id }}">{!! $row->data !!}</div>@endforeach