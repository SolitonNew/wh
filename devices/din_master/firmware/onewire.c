/*
 * onewire.c
 *
 * Created: 07.03.2021 13:32:46
 *  Author: User
 */ 

#include <avr/io.h>
#include "onewire.h"

void onewire_init(void) {
	ONEWIRE_DDR &= ~(1<<ONEWIRE_PIN);
}