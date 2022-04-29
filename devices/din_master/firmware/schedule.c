/*
 *  Author: Moklyak Alexandr
 */

#include "board.h"
#include "schedule.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include <math.h>
#include "core.h"
#include "control.h"

#define SCHEDULE_INTERVAL F_CPU / 256 / 256

typedef struct _schedule {
    int index;
    int value_int;
    int duration;
} schedule_t;

schedule_t schedule_list[SCHEDULE_LIST_MAX];
uint16_t schedule_counter = 0;

ISR(TIMER0_OVF_vect) {
    schedule_counter++;
}

void schedule_init(void) {
    TCCR0 = (1<<CS02);  // 256
    TIMSK = (1<<TOIE0);
}

/**
 * Handling Deferred Variable Value Assignments
 * should be called once every 1 sec.
 */
void schedule_processing(void) {
    if (schedule_counter < SCHEDULE_INTERVAL) return ;
    schedule_counter = 0;
    
    for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
        schedule_t *rec = &schedule_list[i];
        if (rec->duration > 0) {
            rec->duration--;
            if (rec->duration == 0) { // ��������� ��������
                core_set_variable_value_int(rec->index, 3, rec->value_int);
            }
        }
    }
}

/**
 * Registering a delayed assignment of a variable value.
 */
void schedule_variable_value(int index, float value, int duration) {
    for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
        schedule_t *rec = &schedule_list[i];
        if (rec->duration > 0 && rec->index == index) {
            rec->value_int = ceil(value * 10);
            rec->duration = duration;
            return ;
        }
    }
    
    for (uint8_t i = 0; i < SCHEDULE_LIST_MAX; i++) {
        schedule_t *rec = &schedule_list[i];
        if (rec->duration == 0) {
            rec->index = index;
            rec->value_int = ceil(value * 10);
            rec->duration = duration;
            return ;
        }
    }
}
