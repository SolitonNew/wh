/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>
#include "../onewire.h"
#include "hs.h"

uint8_t hs_get_data(uint8_t *rom, hs_data_t *data) {
    if (!onewire_reset()) return 0;
    
    onewire_match_rom(rom);
    onewire_write_byte(HS_READ_DATA);
    
    uint8_t d[2];	
    for (uint8_t i = 0; i < 2; i++) {
        d[i] = onewire_read_byte();
    }
    
    uint8_t crc = 0;
    for (uint8_t i = 0; i < 2; i++) {
        crc = onewire_crc_table(crc ^ d[i]);
    }
    
    if (crc == 0) {
        if (d[0] & HS_BUTTON_LEFT) {
            data->left = 1;
        } else {
            data->left = 0;
        }
        
        if (d[0] & HS_BUTTON_RIGHT) {
            data->right = 1;
        } else {
            data->right = 0;
        }
        
        return 1;
    }
    
    return 0;
}
