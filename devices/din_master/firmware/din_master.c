/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include "util/delay.h"
#include "core.h"
#include "control.h"
#include "config/devs.h"

//#include "lcd.h"

control_btn_states_t control_btn_states = {0, 0, 0, 0};
	
uint8_t roms[80];
char text[16];
	
int main(void)
{
	controller_id = 1;
	
	control_init();
	core_init();
	
	//lcd_init();
	//lcd_text("START", 5);
			
	sei();
		
    while(1) {
		//core_onewire_alarm_processing();
		//core_schedule_processing();
		
		// Обработка кнопок управления
		control_check_btn(&control_btn_states);
		if (control_btn_states.btn_1) {
			control_led_r(1);
		}
		
		// ---------------------------
		
		_delay_ms(10);
    }
}

