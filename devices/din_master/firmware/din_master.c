/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include "util/delay.h"
#include "variables.h"
#include "rs485.h"
#include "onewire.h"
#include "config.h"
#include "drivers/ds18b20.h"
#include "config.h"

#include "lcd.h"

unsigned char alarm_roms[ONEWIRE_ALARM_LIMIT * 8]; // 20 ow devs

int main(void)
{
	SPIN(DDRC, 5);
	CPIN(PORTC, 5);
	
	lcd_init();		
	rs485_init();
	onewire_init();
	
    while(1)
    {	
		int index = 0;
		unsigned char ow_num = onewire_search(alarm_roms);
		for (unsigned char i = 0; i < ow_num; i++) {
			ds18b20_start_measure(&alarm_roms[index]);
			index += 8;
		}
		
		_delay_ms(750);
		unsigned char buff[16] = {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};
		lcd_clear();
		index = 0;
		for (unsigned char i = 0; i < ow_num; i++) {
			lcd_char(':');
			unsigned char buff[16] = {0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};
			sprintf(buff, "%d", (int)onewire_get_value(&alarm_roms[index]));
			lcd_text(buff, 16);
			index += 8;
		}
		
		_delay_ms(10);
    }
}

