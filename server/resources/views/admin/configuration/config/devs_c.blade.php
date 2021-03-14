// ---------------------------------------
//
//     Этот файл создан автоматически
//
// ---------------------------------------


#include <avr/pgmspace.h>
#include "devs.h"

const int onewire_roms_count = {{ count($owList) }};

const uint8_t onewire_roms[{{ count($owList) * 8 }}] PROGMEM = {
@foreach($owList as $row)
    {{ sprintf("0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X,", 
                $row->rom_1, $row->rom_2, $row->rom_3, $row->rom_4, $row->rom_5, $row->rom_6, $row->rom_7, $row->rom_8) }}
@endforeach
};

const int variable_count = {{ count($varList) }};

const variable_t variables[{{ count($varList) }}] PROGMEM = {
@foreach($varList as $row)
   { {{ $row->id }}, {{ $row->controller_id }}, {{ $varTyps[$row->typ] }}, {{ $row->direction }}, {{ $row->ow_index }}, 0 },
@endforeach
};

float variable_values[{{ count($varList) }}];