/*
 * ds18b20.c
 *
 * Created: 07.03.2021 20:03:01
 *  Author: User
 */ 

#include "../onewire.h"
#include "ds18b20.h"

void ds18b20_start_measure(uint8_t *rom) {
	if (!onewire_reset()) return;
	onewire_match_rom(rom);
	onewire_write_byte(ONEWIRE_CONVERTTEMP);
}

float ds18b20_get_value(uint8_t *rom) {
	if (!onewire_reset()) return 0;
	
	uint8_t d[9];	
		
	onewire_match_rom(rom);
	onewire_write_byte(ONEWIRE_RSCRATCHPAD);	
	for (uint8_t i = 0; i < 9; i++) {
		d[i] = onewire_read_byte();
	}		
	
	uint8_t crc = 0;
	for (uint8_t i = 0; i < 9; i++) {
		crc = onewire_crc_table(crc ^ d[i]);
	}
	
	if (crc == 0) {
		return ((d[1] << 8 | d[0]) / 16.0);
	}		
	
	return 0;
}

void ds18b20_set_value(uint8_t *rom, float val) {
	// readonly
}