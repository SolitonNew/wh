/*
 * ds18b20.c
 *
 * Created: 07.03.2021 20:03:01
 *  Author: User
 */ 

#include "../onewire.h"
#include "ds18b20.h"

void ds18b20_start_measure(unsigned char *rom) {
	if (!onewire_reset()) return 0;
	onewire_match_rom(rom);
	onewire_write_byte(ONEWIRE_CONVERTTEMP);
}

float ds18b20_get_value(unsigned char *rom) {
	if (!onewire_reset()) return 0;
	
	unsigned char d[9];	
		
	onewire_match_rom(rom);
	onewire_write_byte(ONEWIRE_RSCRATCHPAD);	
	for (unsigned char i = 0; i < 9; i++) {
		d[i] = onewire_read_byte();
	}		
	
	unsigned char crc = 0;
	for (unsigned char i = 0; i < 9; i++) {
		crc = onewire_crc_table(crc ^ d[i]);
	}
	
	if (crc == 0) {
		return ((d[1] << 8 | d[0]) / 16.0);
	}		
	
	return 0;
}

void ds18b20_set_value(unsigned char *rom, float val) {
	// readonly
}