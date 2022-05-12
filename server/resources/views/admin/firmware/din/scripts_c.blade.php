// ---------------------------------------
//
//   This file was created automatically
//
// ---------------------------------------


#include "../commands.h"
#include <math.h>

@foreach($scriptList as $row)
void script_{{ $row->id }}(void) {
{!! $row->data_to_c !!}
}

@endforeach

void script_run_event_for_variable(int index) {
    switch (index) {
@foreach($eventList as $row)
        case {{ $row->variableIndex }}: 
            @foreach(explode(',', $row->script_ids) as $scr)
            script_{{ $scr }}();
            @endforeach
            break;
            
@endforeach
        default: ;
    }
}