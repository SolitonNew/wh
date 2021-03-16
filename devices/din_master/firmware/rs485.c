/*
 * rs485.c
 *
 * Created: 07.03.2021 13:34:26
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include "util/delay.h"
#include "rs485.h"
#include "lcd.h"

uint8_t rs485_in_buff[RS485_BUFF_MAX_SIZE];
uint8_t rs485_in_buff_size = 0;

void rs485_init(void) {
	unsigned int ubrr = RS485_UBRR;
	
	// Частота
    UBRRH = (uint8_t)(ubrr>>8);
	UBRRL = (uint8_t)ubrr;
	
	// Включаем
	UCSRB = (1<<RXCIE) | (1<<RXEN) | (1<<TXEN);
	
	// 8bit  2 stop bits 
	UCSRC = (1<<URSEL) | (1<<UCSZ0) | (1<<UCSZ1) | (1<<USBS);
}

uint8_t rs485_crc_table(uint8_t data) {
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

void rs485_write_byte(uint8_t c) {
    while (!(UCSRA & (1<<UDRE))) ;
    UDR = c;
}

void rs485_transmit_CMD(uint8_t cmd, int tag) {
    rs485_cmd_pack_t pack;
    memcpy(pack.sign, "CMD", 3);
    pack.controller_id = controller_id;
    pack.cmd = cmd;
    pack.tag = tag;
    uint8_t *ind = &pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
}

void rs485_transmit_VAR(int id, float value) {
    rs485_var_pack_t pack;
    memcpy(pack.sign, "VAR", 3);
    pack.controller_id = controller_id;
    pack.id = id;
    pack.value = value;
    uint8_t *ind = &pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
}

void rs485_transmit_ROM(uint8_t *rom) {
    rs485_ow_rom_pack_t pack;
    memcpy(pack.sign, "ROM", 3);
    pack.controller_id = controller_id;
    memcpy(pack.rom, rom, 8);
    uint8_t *ind = &pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
}

ISR(USART__RXC_vect) {
	// Накапливаем входящий буфер
	if (rs485_in_buff_size >= RS485_BUFF_MAX_SIZE - 1) {
		rs485_in_buff_size = 0;
		board_rs485_error();
	}
	rs485_in_buff[rs485_in_buff_size++] = UDR;
	
	
}