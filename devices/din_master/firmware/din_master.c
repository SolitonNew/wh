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

const int variable_count;
control_btn_states_t control_btn_states = {0, 0, 0, 0, 0, 0, 0, 0};
    
uint8_t alarm_loop_space = 0;
	
int main(void)
{
	controller_id = 1;
	
	control_init();
	core_init();
				
	sei();
		
    while (1) {
        // Обрабатываем входной буфер
        core_rs485_processing();
        
        // Обрабатываем onewire на предмет alarm флагов
        if (alarm_loop_space-- == 0) {
		    core_onewire_alarm_processing();
            alarm_loop_space = 10;
        }
        
        // Обрабатываем работу с запланироваными устройствами
		core_schedule_processing();
		
		// Обработка кнопок управления
		control_check_btn(&control_btn_states);
        
        if (control_btn_states.btn_1_change && control_btn_states.btn_1_down == 0) {
            board_reset();
        }
        
        if (control_btn_states.btn_2_change) {
            //
        }   
        
        if (control_btn_states.btn_3_change) {
            //
        }                     
        
		if (control_btn_states.btn_4_change) {
			control_led_r(0);
            control_led_b(0);
		}
		
		// ---------------------------
        
        //control_led_b(controller_initialized);
		
		_delay_ms(10);
    }
}

