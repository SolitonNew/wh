@extends('index')

@section('head')
<style>
    .end-day {
        border-bottom: 2px solid #0000ff;
    }
    
    .first-col {
        border-left: 2px solid #0000ff!important;
    }
</style>
@endsection

@section('body')
<table class="table table-sm table-bordered table-hover">
    <thead>
        <tr>
            <th>Time</th>
            <th colspan="2" class="first-col">TEMP</th>
            <th colspan="2" class="first-col">P</th>
            <th colspan="2" class="first-col">CC</th>
            <th colspan="2" class="first-col">G</th>
            <th colspan="2" class="first-col">H</th>
            <th colspan="2" class="first-col">V</th>
            <th colspan="2" class="first-col">WD</th>
            <th colspan="2" class="first-col">WS</th>
            <th colspan="2" class="first-col">MP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr class="{{ $row->time->format('H') == '23' ? 'end-day' : '' }}">
            <th>{{ $row->time->format('d-m H:i') }}</th>
            <td class="first-col">{{ $row->TEMP }}</td><td>{{ $row->TEMP_2 }}</td>
            <td class="first-col">{{ $row->P }}</td><td>{{ $row->P_2 }}</td>
            <td class="first-col">{{ $row->CC }}</td><td>{{ $row->CC_2 }}</td>
            <td class="first-col">{{ $row->G }}</td><td>{{ $row->G_2 }}</td>
            <td class="first-col">{{ $row->H }}</td><td>{{ $row->H_2 }}</td>
            <td class="first-col">{{ $row->V }}</td><td>{{ $row->V_2 }}</td>
            <td class="first-col">{{ $row->WD }}</td><td>{{ $row->WD_2 }}</td>
            <td class="first-col">{{ $row->WS }}</td><td>{{ $row->WS_2 }}</td>
            <td class="first-col">{{ $row->MP }}</td><td>{{ $row->MP_2 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection()