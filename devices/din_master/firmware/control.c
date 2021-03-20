/*
 * led.c
 *
 * Created: 10.03.2021 17:44:09
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <string.h>
#include "control.h"

void control_init(void) {
	// LED
	SPIN(CONTROL_LED_R_DDR, CONTROL_LED_R_BIT);
	CPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	
	SPIN(CONTROL_LED_G_DDR, CONTROL_LED_G_BIT);
	CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	
	SPIN(CONTROL_LED_Y_DDR, CONTROL_LED_Y_BIT);
	CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	
	SPIN(CONTROL_LED_B_DDR, CONTROL_LED_B_BIT);
	CPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
	
	// BTN
	CPIN(CONTROL_BTN_1_DDR, CONTROL_BTN_1_BIT);
	SPIN(CONTROL_BTN_1_PORT, CONTROL_BTN_1_BIT);
	
	CPIN(CONTROL_BTN_2_DDR, CONTROL_BTN_2_BIT);
	SPIN(CONTROL_BTN_2_PORT, CONTROL_BTN_2_BIT);
	
	CPIN(CONTROL_BTN_3_DDR, CONTROL_BTN_3_BIT);
	SPIN(CONTROL_BTN_3_PORT, CONTROL_BTN_3_BIT);
	
	CPIN(CONTROL_BTN_4_DDR, CONTROL_BTN_4_BIT);
	SPIN(CONTROL_BTN_4_PORT, CONTROL_BTN_4_BIT);
}

void control_led_r(uint8_t new_state) {
    if (new_state == 2) {
        if (GPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT)) {
            CPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
        } else {
            SPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
        }
    } else
	if (new_state) {
		SPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	} else {
		CPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	}
}

void control_led_g(uint8_t new_state) {
    if (new_state == 2) {
        if (GPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT)) {
            CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
        } else {
            SPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
        }
    } else        
	if (new_state) {
		SPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	} else {
		CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	}
}

void control_led_y(uint8_t new_state) {
    if (new_state == 2) {
        if (GPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT)) {
            CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
        } else {
            SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
        }
    } else
	if (new_state) {
		SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	} else {
		CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	}
}

void control_led_b(uint8_t new_state) {
    if (new_state == 2) {
        if (GPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT)) {
            CPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
        } else {
            SPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
        }
    } else
	if (new_state) {
		SPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
	} else {
		CPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
	}
}

void control_check_btn(control_btn_states_t *states) {
    control_btn_states_t prev_states;
    memcpy(&prev_states, states, sizeof(prev_states));
    
	if (GPIN(CONTROL_BTN_1_PIN, CONTROL_BTN_1_BIT)) {
		states->btn_1_down = 0;
	} else {
		states->btn_1_down = 1;
	}
    
    states->btn_1_change = (states->btn_1_down != prev_states.btn_1_down);
	
	if (GPIN(CONTROL_BTN_2_PIN, CONTROL_BTN_2_BIT)) {
		states->btn_2_down = 0;
	} else {
		states->btn_2_down = 1;
	}
    
    states->btn_2_change = (states->btn_2_down != prev_states.btn_2_down);
	
	if (GPIN(CONTROL_BTN_3_PIN, CONTROL_BTN_3_BIT)) {
		states->btn_3_down = 0;
	} else {
		states->btn_3_down = 1;
	}
    
    states->btn_3_change = (states->btn_3_down != prev_states.btn_3_down);
	
	if (GPIN(CONTROL_BTN_4_PIN, CONTROL_BTN_4_BIT)) {
		states->btn_4_down = 0;
	} else {
		states->btn_4_down = 1;
	}
    
    states->btn_4_change = (states->btn_4_down != prev_states.btn_4_down);
}