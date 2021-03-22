/*
 *  Author: Moklyak Alexandr
 */ 

#include <math.h>
#include "../onewire.h"
#include "ds18b20.h"

void ds18b20_start_measure(uint8_t *rom) {
    if (!onewire_reset()) return ;
    onewire_match_rom(rom);
    onewire_write_byte(ONEWIRE_CONVERTTEMP);
}

uint8_t ds18b20_get_data(uint8_t *rom, ds18b20_data_t *data) {
    if (!onewire_reset()) return 0;
    
    onewire_match_rom(rom);
    onewire_write_byte(ONEWIRE_RSCRATCHPAD);	
    
    uint8_t d[9];	
    for (uint8_t i = 0; i < 9; i++) {
        d[i] = onewire_read_byte();
    }		
    
    uint8_t crc = 0;
    for (uint8_t i = 0; i < 9; i++) {
        crc = onewire_crc_table(crc ^ d[i]);
    }
    
    if (crc == 0) {
        data->temp = ceil(((d[1] << 8 | d[0]) / 16.0) * 10) / 10;
        return 1;
    }		
	
    return 0;
}
