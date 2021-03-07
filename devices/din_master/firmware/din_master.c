/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#define F_CPU 9600000UL
#include <avr/io.h>
#include "util/delay.h"
<<<<<<< HEAD
//#include "onewire.h"
#include "_config.h"


void command_set(char name[], float value) {
	
}
=======
#include "variables.h"
#include "rs485.h"
#include "onewire.h"
#include "config/scripts.h"
>>>>>>> ab5a208959b97b7e6e50dcb4c9a7438b438c9c2d

int main(void)
{
	rs485_init();
	onewire_init();
	
	command_set(0, 1, 10);
	
    while(1)
    {
<<<<<<< HEAD
=======
		
		
>>>>>>> ab5a208959b97b7e6e50dcb4c9a7438b438c9c2d
        _delay_us(1);
    }
}

