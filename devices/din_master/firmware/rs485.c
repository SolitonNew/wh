/*
 * rs485.c
 *
 * Created: 07.03.2021 13:34:26
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include "rs485.h"

#define UBRR F_CPU / 16 / 19200 - 1

void rs485_init(void) {
	unsigned int ubrr = UBRR;
	
	// Частота
    UBRRH = (uint8_t)(ubrr>>8);
    UBRRL = (uint8_t)ubrr;
	
	// Включаем
	UCSRB = (1<<RXEN) | (1<<TXEN);
	
	//                            8bit            2 stop bits 
	UCSRC = (1<<URSEL) | (1<<UCSZ0) | (1<<UCSZ1) | (1<<USBS);
}

void rs485_sync(void) {
	
}

void rs485_send(uint8_t c) {
    while (!(UCSRA & (1<<UDRE))) {}	
    UDR = c;
}

uint8_t rs485_check(void) {
	while(!(UCSRA & (1<<RXC))) {}
	return UDR;
}