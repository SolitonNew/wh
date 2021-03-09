#include <avr/pgmspace.h>
#include "variables.h"

const unsigned char ow_roms[{{ count($owList) * 8 }}] PROGMEM = {
@foreach($owList as $row)
    {{ sprintf("0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X,", 
                $row->ROM_1, $row->ROM_2, $row->ROM_3, $row->ROM_4, $row->ROM_5, $row->ROM_6, $row->ROM_7, $row->ROM_8) }}
@endforeach
};

const struct variable[{{ count($varList) }}] PROGMEM = {
@foreach($varList as $row)
   { {{ $row->ID }}, {{ $row->CONTROLLER_ID }}, {{ $varTyps[$row->ROM] }}, {{ $row->DIRECTION }}, {{ $row->OW_INDEX }}, 0 },
@endforeach
};

extern int variable_values[{{ count($varList) }}];

@foreach($scriptList as $row)
void script_{{ $row->ID }}(void) {
{!! $row->DATA_TO_C !!}
}
@endforeach