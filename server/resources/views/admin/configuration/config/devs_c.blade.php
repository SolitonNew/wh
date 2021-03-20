// ---------------------------------------
//
//     Этот файл создан автоматически
//
// ---------------------------------------


#include <avr/pgmspace.h>
#include "devs.h"

const uint8_t onewire_roms[ONEWIRE_ROMS_SIZE] PROGMEM = {
@foreach($owList as $row)
    {{ sprintf("0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X, 0x%'02X,", 
                $row->rom_1, $row->rom_2, $row->rom_3, $row->rom_4, $row->rom_5, $row->rom_6, $row->rom_7, $row->rom_8) }}
@endforeach
};

const variable_t variables[VARIABLE_COUNT] PROGMEM = {
@foreach($varList as $row)
   { {{ $row->id }}, {{ $row->controller_id }}, {{ $varTyps[$row->typ] }}, {{ $row->direction }}, {{ $row->ow_index }}, 0 },
@endforeach
};

float variable_values[VARIABLE_COUNT];

int devs_get_variable_index(int id) {
    for (int i = 0; i < VARIABLE_COUNT; i++) {
        if ((int)pgm_read_dword(&variables[i]) == id) {
            return i;
        }
    }
    return -1;
}

uint8_t devs_get_varible(int index, variable_t *variable) {	
    uint8_t* ind = (uint8_t*)(&variables[index]);
    uint8_t* v = (uint8_t*)variable;
    for (uint8_t i = 0; i < sizeof(variable_t); i++) {
        (*v++) = pgm_read_byte(ind++);
    }
    return 1;
}

int devs_get_variable_ow_index(int index) {
    variable_t v;
    uint8_t off = (uint8_t*)(&v.ow_index) - (uint8_t*)(&v);
    return pgm_read_word((uint8_t*)&variables[index] + off);
}

uint8_t devs_get_variable_controller(int index) {
    variable_t v;
    uint8_t off = (uint8_t*)(&v.controller_id) - (uint8_t*)(&v);
    return pgm_read_word((uint8_t*)&variables[index] + off);
}

void devs_get_ow_rom(int ow_index, uint8_t *rom) {
    if (ow_index > -1) {
        uint8_t* ind = (uint8_t*)&onewire_roms[ow_index * 8];
        for (uint8_t i = 0; i < 8; i++) {
            rom[i] = pgm_read_byte(ind++);
        }
    } else {
        rom[0] = 0;
    }
}

void devs_get_variable_rom(int index, uint8_t *rom) {
    int ow_index = devs_get_variable_ow_index(index);
	devs_get_ow_rom(ow_index, rom);
}

int devs_onewire_rom_index(uint8_t *rom) {
    uint8_t* ind = (uint8_t*)&onewire_roms[0];
    for (int i = 0; i < ONEWIRE_ROMS_COUNT; i++) {
        for (int r = 0; r < 8; r++) {
            uint8_t b = pgm_read_byte(ind + r);
            if (rom[r] == b) {
                if (r == 7) {
                    return i;
                }
            } else {
                break;
            }
        }
        ind += 8;
    }
    return -1;
}

uint8_t devs_find_variables_by_ow_index(int ow_index, int *vars) {
    uint8_t num = 0;
    for (int i = 0; i < VARIABLE_COUNT; i++) {
        if (devs_get_variable_ow_index(i) == ow_index) {
            vars[num++] = i;
            if (num == 8) break;
        }
    }
    return num;
}