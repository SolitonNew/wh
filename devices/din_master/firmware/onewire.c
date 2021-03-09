/*
 * onewire.c
 *
 * Created: 07.03.2021 13:32:46
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include "util/delay.h"
#include "onewire.h"
#include "drivers/ds18b20.h"

#define ONEWIRE_checkIn ONEWIRE_PIN & (1<<ONEWIRE_BIT)

void onewire_init(void) {
	ONEWIRE_DDR &= ~(1<<ONEWIRE_BIT);
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

void onewire_write_bit(unsigned char bit) {
	onewire_set(1);
	_delay_us(1);
	if(bit) onewire_set(0); 
	_delay_us(60);	
	onewire_set(0);
}

unsigned char onewire_read_bit(void) {
	unsigned char bit = 0;
	onewire_set(1);
	_delay_us(1);	
	onewire_set(0);
	_delay_us(10);
	if (ONEWIRE_checkIn) bit = 1;
	_delay_us(40);
	return bit;
}

void onewire_write_byte(unsigned char byte) {
	for (unsigned char i = 0; i < 8; i++) {
		onewire_write_bit(byte & (1<<i));
	}		
}

unsigned char onewire_read_byte(void) {
	unsigned char n = 0;
	for (unsigned char i = 0; i < 8; i++) {
		if (onewire_read_bit()) {
			n |= (1<<i);
		}
	}
	return n;
}

unsigned char onewire_search_rom(unsigned char *last_rom, unsigned char diff, unsigned char cmd) {
	if (!onewire_reset()) {
		last_rom[0] = 0;
		return 0;
	}
	onewire_write_byte(cmd);	
	unsigned char rom[8] = {0,0,0,0,0,0,0,0};
    unsigned char next_diff = 0;
    unsigned char i = 64;
    for (unsigned char byte = 0; byte < 8; byte++) {
        unsigned char r_b = 0;
        for (unsigned char bit = 0; bit < 8; bit++) {
            unsigned char b = onewire_read_bit();
            if (onewire_read_bit()) {                    
                if (b) { // There are no devices or there is a mistake on the wire
					last_rom[0] = 0;
                    return 0;
				}					
            } else {               
                if (!b) { // Collision. Two devices with different bit meaning
                    if (diff > i || ((last_rom[byte] & (1 << bit)) && (diff != i))) {
                        b = 1;
                        next_diff = i;
					}					
				}
			}		
			onewire_write_bit(b);
            if (b) {
                r_b |= (1 << bit);
			}				
            i -= 1;
		}
        rom[byte] = r_b;
	}
	
	for (unsigned char i = 0; i < 8; i++) {
		last_rom[i] = rom[i];
	}
		
	return next_diff;
}

unsigned char onewire_search_roms(unsigned char cmd, unsigned char *roms, unsigned char limit) {	
	unsigned char num = 0;
	unsigned char rom[8] = {0,0,0,0,0,0,0,0};
	unsigned char diff = 65;
	
	for (unsigned char i = 0; i < 0xff; i++) {
		diff = onewire_search_rom(rom, diff, cmd);
		if (rom[0]) {
			memcpy(&roms[num * 8], rom, 8);
			num++;
			if (num > limit) break;
		}
		if (diff == 0) break;
	}
	
	return num;
}

unsigned char onewire_match_rom(unsigned char *rom) {
	onewire_write_byte(ONEWIRE_MATCH_ROM);	
	for (unsigned char i = 0; i < 8; i++) {
		onewire_write_byte(rom[i]);
	}		
	return 1;
}

unsigned char onewire_search(unsigned char *roms) {
	return onewire_search_roms(ONEWIRE_SEARCH_ROM, roms, 20);
}

unsigned char onewire_alarms(unsigned char *roms) {
	return onewire_search_roms(ONEWIRE_ALARM_SEARCH, roms, 20);
}

float onewire_get_value(unsigned char *rom) {
	switch (rom[0]) {
		case 0x28:
			return ds18b20_get_value(rom);
		case 0xf0:
			return hs_get_value(rom);
	}
	
	return 1;
}

void onewire_set_value(unsigned char *rom, float val) {
	switch (rom[0]) {
		case 0x28:
			ds18b20_set_value(&rom[0], val);
			break;
		case 0xf0:
			hs_set_value(rom, val);
			break;
	}
}
