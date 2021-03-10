#include <avr/pgmspace.h>
#include "devs.h"

const uint8_t ow_roms[{{ count($owList) * 8 }}] PROGMEM = {
@foreach($owList as $row)
    {{ sprintf("0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X,", 
                $row->ROM_1, $row->ROM_2, $row->ROM_3, $row->ROM_4, $row->ROM_5, $row->ROM_6, $row->ROM_7, $row->ROM_8) }}
@endforeach
};

typedef struct _variable_t {
	int id;
	unsigned char ctrl_id;
	unsigned char typ;
	unsigned char direction;
	int ow_index;
	unsigned char channel;
} variable_t;

const variable_t variables[{{ count($varList) }}] PROGMEM = {
@foreach($varList as $row)
   { {{ $row->ID }}, {{ $row->CONTROLLER_ID }}, {{ $varTyps[$row->ROM] }}, {{ $row->DIRECTION }}, {{ $row->OW_INDEX }}, 0 },
@endforeach
};

int variable_count = {{ count($varList) }};
float variable_values[{{ count($varList) }}];