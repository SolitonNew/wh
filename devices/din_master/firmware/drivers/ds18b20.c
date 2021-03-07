/*
 * ds18b20.c
 *
 * Created: 07.03.2021 20:03:01
 *  Author: User
 */ 

#include "../onewire.h"
#include "ds18b20.h"

void ds18b20_startMeasure(unsigned char *rom) {
	if (!onewire_reset()) return 0;
	onewire_matchROM(rom);
	onewire_writeByte(ONEWIRE_CONVERTTEMP);
}

float ds18b20_get(unsigned char *rom) {
	if (!onewire_reset()) return 0;
	
	unsigned char d[9];	
		
	onewire_matchROM(rom);
	onewire_writeByte(ONEWIRE_RSCRATCHPAD);	
	for (unsigned char i = 0; i < 9; i++) {
		d[i] = onewire_readByte();
	}		
	
	unsigned char crc = 0;
	for (unsigned char i = 0; i < 9; i++) {
		crc = onewire_crc_table(crc ^ d[i]);
	}		
	
	if (crc == 0) {
		return ceil(((d[1] << 8 | d[0]) / 16.0) * 10);
	}		
	
	return 0;
}

void ds18b20_set(unsigned char *rom, float value) {
	// readonly
}