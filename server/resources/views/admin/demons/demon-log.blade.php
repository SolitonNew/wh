@foreach($data as $row)
<div class="demon-log-line" data-id="{{ $row->id }}">{!! $row->data !!}</div>@endforeach