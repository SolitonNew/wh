/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include <util/delay.h>
#include "core.h"
#include "control.h"
#include "rs485.h"
#include "lcd.h"

control_btn_states_t control_btn_states = {0, 0, 0, 0};
char text[16];
	
int main(void)
{
	controller_id = 1;
	
	control_init();
	core_init();
    lcd_init();
				
	sei();
		
    while (1) {
        lcd_clear();
        uint8_t num = sprintf(text, "P:%d  E:%d", rs485_packs, rs485_errors);
        lcd_move(2, 3);
        lcd_text(text, num);
        
		core_onewire_alarm_processing();
		core_schedule_processing();
		
		// Обработка кнопок управления
		control_check_btn(&control_btn_states);
		if (control_btn_states.btn_1) {
			control_led_r(1);
		}
		
		// ---------------------------
		
		_delay_ms(10);
    }
}

