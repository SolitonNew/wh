/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include "util/delay.h"
#include "core.h"
#include "control.h"

#include "lcd.h"

uint8_t controller_id;
control_btn_states_t control_btn_states = {0, 0, 0, 0};

int main(void)
{
	controller_id = 1;
	
	control_init();
	core_init();

	lcd_init(); 
	
    while(1)
    {	
		core_rs485_processing();
		core_onewire_alarm_processing();
		core_schedule_processing();
		
		// Обработка кнопок управления
		control_check_btn(&control_btn_states);
		if (control_btn_states.btn_1) {
			control_led_r(1);
		}
		
		// -------------------------------
		lcd_clear();
		uint8_t buff[8];
		core_get_variable_rom(1, buff);
		for (uint8_t i = 0; i < 8; i++) {
			lcd_hex(buff[i]);
			lcd_char(' ');
		}
		// -------------------------------
		
		_delay_ms(10);
    }
}

