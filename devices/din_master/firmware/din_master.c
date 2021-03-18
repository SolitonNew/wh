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
	
int main(void)
{
	controller_id = 1;
	
	control_init();
	core_init();
    lcd_init();
				
	sei();
		
    while (1) {
        // ������������ ������� �����
        core_rs485_processing();
        
        // ������������ onewire �� ������� alarm ������
		core_onewire_alarm_processing();
        
        // ������������ ������ � ��������������� ������������
		core_schedule_processing();
		
		// ��������� ������ ����������
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
		
		_delay_ms(10);
    }
}

