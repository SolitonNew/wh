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

void rs485_sync(void) {
	
}

void rs485_send(uint8_t c) {
    while (!(UCSRA & (1<<UDRE))) ;
    UDR = c;
}

ISR(USART__RXC_vect) {
	// Накапливаем входящий буфер
	if (rs485_in_buff_size >= RS485_BUFF_MAX_SIZE - 1) {
		rs485_in_buff_size = 0;
		board_rs485_error();
	}
	rs485_in_buff[rs485_in_buff_size++] = UDR;
	
	
}