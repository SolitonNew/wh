/*
 *  Author: Moklyak Alexandr
 */ 

#include "../onewire.h"
#include "dht11.h"

uint8_t dht11_get_data(uint8_t *rom, dht11_data_t *data) {
    if (!onewire_reset()) return 0;
    
    onewire_match_rom(rom);
    onewire_write_byte(ONEWIRE_READ_DATA);	
    
    uint8_t d[3];
    d[0] = onewire_read_byte();
    d[1] = onewire_read_byte();
    d[2] = onewire_read_byte();
    
    uint8_t crc = 0;
    for (uint8_t i = 0; i < 3; i++) {
        crc = onewire_crc_table(crc ^ d[i]);
    }
    
    if (crc == 0) {
        data->h = d[0];
        data->t = d[1];
        return 1;
    }
    
    return 0;
}
