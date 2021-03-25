/*
 *  Author: Moklyak Alexandr
 */

#include "board.h"
#include "schedule.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include "core.h"
#include "control.h"

typedef struct _schedule {
	int index;
	int value;
	int duration;
} schedule_t;

schedule_t schedule_list[SCHEDULE_LIST_MAX];
uint16_t schedule_counter = 0;

ISC(TIMER0_OVF_vect) {
	schedule_counter++;
}

void schedule_init(void) {
	TCCR0 = (1<<CS00) | (1<<CS02);
	TIMSK = (1<<TOIE0);
}	

/**
 * Обработка отложенных назначений значения переменной
 * Должно вызываться раз в 1 сек.
 */
void schedule_processing(void) {
	if (schedule_counter < 1000) return ;
	schedule_counter = 0;
	
	if (GPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT)) {
		CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	} else {
		SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	}
	
	for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
		schedule_t *rec = &schedule_list[i];
		if (rec->duration > 0) {
			if (rec->duration-- == 0) { // Выполняем действие
				core_set_variable_value_int(rec->index, 3, rec->value);
			}
		}
	}
}

/**
 * Регистрация отложенного назначения значения переменной.
 */
void schedule_variable_value(int index, float value, int duration) {
	for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
		schedule_t *rec = &schedule_list[i];
		if (rec->duration > 0 && rec->index == index) {
			rec->value = ceil(value * 10);
			rec->duration = duration;
			return ;
		}
	}
	
	for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
		schedule_t *rec = &schedule_list[i];
		if (rec->duration == 0) {
			rec->index = index;
			rec->value = value;
			rec->duration = duration;
			return ;
		}
	}
}
