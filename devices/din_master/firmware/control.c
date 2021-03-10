/*
 * led.c
 *
 * Created: 10.03.2021 17:44:09
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
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
	if (new_state) {
		SPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	} else {
		CPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	}
}

void control_led_g(uint8_t new_state) {
	if (new_state) {
		SPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	} else {
		CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	}
}

void control_led_y(uint8_t new_state) {
	if (new_state) {
		SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	} else {
		CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	}	
}

void control_led_b(uint8_t new_state) {
	if (new_state) {
		SPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
	} else {
		CPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
	}
}

void control_check_btn(control_btn_states_t *states) {
	if (GPIN(CONTROL_BTN_1_PIN, CONTROL_BTN_1_BIT)) {
		states->btn_1 = 1;
	} else {
		states->btn_1 = 0;
	}
	
	if (GPIN(CONTROL_BTN_2_PIN, CONTROL_BTN_2_BIT)) {
		states->btn_2 = 1;
	} else {
		states->btn_2 = 0;
	}
	
	if (GPIN(CONTROL_BTN_3_PIN, CONTROL_BTN_3_BIT)) {
		states->btn_3 = 1;
	} else {
		states->btn_3 = 0;
	}
	
	if (GPIN(CONTROL_BTN_4_PIN, CONTROL_BTN_4_BIT)) {
		states->btn_4 = 1;
	} else {
		states->btn_4 = 0;
	}
}