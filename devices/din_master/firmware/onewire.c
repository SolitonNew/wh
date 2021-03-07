/*
 * onewire.c
 *
 * Created: 07.03.2021 13:32:46
 *  Author: User
 */ 

#include <avr/io.h>
#include "util/delay.h"
#include "onewire.h"

#define ONEWIRE_checkIn ONEWIRE_PIN & (1<<ONEWIRE_BIT)

void onewire_init(void) {
	ONEWIRE_DDR &= ~(1<<ONEWIRE_PIN);
}

unsigned char onewire_search(unsigned char *roms) {
	
}

unsigned char onewire_alarms(unsigned char *roms) {
	
}

unsigned char onewire_crc_table(unsigned char data) {
	unsigned char crc = 0x0;
	unsigned char fb_bit = 0;
	for (unsigned char b = 0; b < 8; b++) { 
		fb_bit = (crc ^ data) & 0x01;
		if (fb_bit == 0x01)
			crc = crc ^ 0x18;
		crc = (crc >> 1) & 0x7F;
		if (fb_bit == 0x01) 
			crc = crc | 0x80;
		data >>= 1;
	}
	return crc;
}

void onewire_set(unsigned char mode) {
	if (mode) {
		ONEWIRE_PORT &= ~(1<<ONEWIRE_BIT);
		ONEWIRE_DDR |= (1<<ONEWIRE_BIT);
	} else {
		ONEWIRE_PORT &= ~(1<<ONEWIRE_BIT);
		ONEWIRE_DDR &= ~(1<<ONEWIRE_BIT);
	}
}

unsigned char onewire_reset(void) {
	unsigned char status;
	onewire_set(1);
	_delay_us(480);
	onewire_set(0);
	_delay_us(60);	
	status = ONEWIRE_checkIn;
	_delay_us(420);
	return !status;
}

void onewire_writeBit(unsigned char bit) {
	onewire_set(1);
	_delay_us(1);
	if(bit) onewire_set(0); 
	_delay_us(60);	
	onewire_set(0);
}

unsigned char onewire_readBit(void) {
	unsigned char bit = 0;
	onewire_set(1);
	_delay_us(1);	
	onewire_set(0);
	_delay_us(10);
	if (ONEWIRE_checkIn) bit = 1;
	_delay_us(40);
	return bit;
}

void onewire_writeByte(unsigned char byte) {
	for (unsigned char i = 0; i < 8; i++) {
		onewire_writeBit(byte & (1<<i));
	}		
}

unsigned char onewire_readByte(void) {
	unsigned char n = 0;
	for (unsigned char i = 0; i < 8; i++) {
		if (onewire_readBit()) {
			n |= (1<<i);
		}
	}
	return n;
}

unsigned char onewire_searchROM(unsigned char diff, unsigned char *id) { 	
	unsigned char i, j, next_diff;
	unsigned char b;

	if (!onewire_reset())
		return ONEWIRE_PRESENCE_ERR;           // error, no device found

	onewire_writeByte(ONEWIRE_SEARCH_ROM);     // ROM search command
	next_diff = ONEWIRE_LAST_DEVICE;		   // unchanged on last device
	
	i = 64;
	do {	
		j = 8;
		do { 
			b = onewire_readBit();
			if (onewire_readBit()) {
				if (b)
					return ONEWIRE_DATA_ERR;
			} else { 
				if (!b) { 
					if (diff > i || ((*id & 1) && diff != i)) { 
						b = 1;
						next_diff = i;
					}
				}
			}
			onewire_writeBit(b);
			*id >>= 1;
			if (b) 
				*id |= 0x80;
			i--;
		} while(--j);
		id++;
    } while(i);
	return next_diff;
}

unsigned char onewire_matchROM(unsigned char *rom) {
	onewire_writeByte(ONEWIRE_MATCH_ROM);	
	for (unsigned char i = 0; i < 8; i++) {
		onewire_writeByte(rom[i]);
	}		
	return 1;
}

float onewire_get_value(unsigned char *rom) {
	switch (rom[0]) {
		case 0x28:
			return ds18b20_get(rom);
		case 0xf0:
			return hs_get_value(rom);
	}
}

void onewire_set_value(unsigned char *rom, float value) {
	switch (rom[0]) {
		case 0x28:
			ds18b20_set(rom, value);
			break;
		case 0xf0:
			hs_set_value(rom, value);
			break;
	}
}
