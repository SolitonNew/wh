/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#define F_CPU 9600000UL
#include <avr/io.h>
#include "util/delay.h"
#include "variables.h"
#include "rs485.h"
#include "onewire.h"
#include "config/scripts.h"

int main(void)
{
	rs485_init();
	onewire_init();
	
	command_set(0, 1, 10);
	
    while(1)
    {
		
		
        _delay_us(1);
    }
}
