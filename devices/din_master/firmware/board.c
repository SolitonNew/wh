/*
 * board.c
 *
 * Created: 15.03.2021 20:46:41
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include "control.h"

uint8_t controller_id;

void board_reset(void) {
	WDTCR |= 1<<WDE;
	while (1) ;
}

void board_rs485_error(void) {
	//
}

void board_onewire_error(void) {
	//
}

void board_script_error(void) {
	//
}