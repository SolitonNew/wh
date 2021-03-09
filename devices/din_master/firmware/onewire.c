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

uint8_t onewire_crc_table(uint8_t data) {
	uint8_t crc = 0x0;
	uint8_t fb_bit = 0;
	for (uint8_t b = 0; b < 8; b++) { 
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

void onewire_set(uint8_t mode) {
	if (mode) {
		ONEWIRE_PORT &= ~(1<<ONEWIRE_BIT);
		ONEWIRE_DDR |= (1<<ONEWIRE_BIT);
	} else {
		ONEWIRE_PORT &= ~(1<<ONEWIRE_BIT);
		ONEWIRE_DDR &= ~(1<<ONEWIRE_BIT);
	}
}

uint8_t onewire_reset(void) {
	uint8_t status;
	onewire_set(1);
	_delay_us(480);
	onewire_set(0);
	_delay_us(60);	
	status = ONEWIRE_checkIn;
	_delay_us(420);
	return !status;
}

void onewire_write_bit(uint8_t bit) {
	onewire_set(1);
	_delay_us(1);
	if(bit) onewire_set(0); 
	_delay_us(60);	
	onewire_set(0);
}

uint8_t onewire_read_bit(void) {
	uint8_t bit = 0;
	onewire_set(1);
	_delay_us(1);	
	onewire_set(0);
	_delay_us(10);
	if (ONEWIRE_checkIn) bit = 1;
	_delay_us(40);
	return bit;
}

void onewire_write_byte(uint8_t byte) {
	for (uint8_t i = 0; i < 8; i++) {
		onewire_write_bit(byte & (1<<i));
	}		
}

uint8_t onewire_read_byte(void) {
	uint8_t n = 0;
	for (uint8_t i = 0; i < 8; i++) {
		if (onewire_read_bit()) {
			n |= (1<<i);
		}
	}
	return n;
}

uint8_t onewire_search_rom(uint8_t *last_rom, uint8_t diff, uint8_t cmd) {
	if (!onewire_reset()) {
		last_rom[0] = 0;
		return 0;
	}
	onewire_write_byte(cmd);	
	uint8_t rom[8] = {0,0,0,0,0,0,0,0};
    uint8_t next_diff = 0;
    uint8_t i = 64;
    for (uint8_t byte = 0; byte < 8; byte++) {
        uint8_t r_b = 0;
        for (uint8_t bit = 0; bit < 8; bit++) {
            uint8_t b = onewire_read_bit();
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
	
	for (uint8_t i = 0; i < 8; i++) {
		last_rom[i] = rom[i];
	}
		
	return next_diff;
}

uint8_t onewire_search_roms(uint8_t cmd, uint8_t *roms, uint8_t limit) {	
	uint8_t num = 0;
	uint8_t rom[8] = {0,0,0,0,0,0,0,0};
	uint8_t diff = 65;
	
	for (uint8_t i = 0; i < 0xff; i++) {
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

uint8_t onewire_match_rom(uint8_t *rom) {
	onewire_write_byte(ONEWIRE_MATCH_ROM);	
	for (uint8_t i = 0; i < 8; i++) {
		onewire_write_byte(rom[i]);
	}		
	return 1;
}

uint8_t onewire_search(uint8_t *roms) {
	return onewire_search_roms(ONEWIRE_SEARCH_ROM, roms, 20);
}

uint8_t onewire_alarms(uint8_t *roms) {
	return onewire_search_roms(ONEWIRE_ALARM_SEARCH, roms, 20);
}

float onewire_get_value(uint8_t *rom) {
	switch (rom[0]) {
		case 0x28:
			return ds18b20_get_value(rom);
		case 0xf0:
			return hs_get_value(rom);
	}
	
	return 1;
}

void onewire_set_value(uint8_t *rom, float val) {
	switch (rom[0]) {
		case 0x28:
			ds18b20_set_value(&rom[0], val);
			break;
		case 0xf0:
			hs_set_value(rom, val);
			break;
	}
}
