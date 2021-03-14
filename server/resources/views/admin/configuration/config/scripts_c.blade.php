// ---------------------------------------
//
//     Этот файл создан автоматически
//
// ---------------------------------------


#include "../commands.h"

@foreach($scriptList as $row)
void script_{{ $row->id }}(void) {
{!! $row->data_to_c !!}
}

@endforeach

void runEventScriptForVariable(int index) {
    switch (index) {
@foreach($eventList as $row)
        case {{ $row->variableIndex }}: 
            script_{{ $row->script_id }}();
            break;
            
@endforeach
        default: ;
    }
}