/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>

<<<<<<< HEAD
#define CORE_VARIABLE_CHANGED_COUNT_MAX 32
=======
#define CORE_VARIABLE_CHANGED_COUNT_MAX 30
#define CORE_PERIODIC_STEP_INTERVAL 5000 // usec
#define CORE_PERIODIC_STEP_MAX CORE_PERIODIC_STEP_INTERVAL/MAIN_LOOP_DELAY
#define CORE_SET_LATER_LIST_MAX 32
>>>>>>> 4fb2b5fca43ba0be5c152ccc90089da5d0f363b6

extern int core_variable_changed[CORE_VARIABLE_CHANGED_COUNT_MAX];
extern uint8_t core_variable_changed_count;

void core_init(void);
void core_rs485_processing(void);
void core_onewire_alarm_processing(void);
void core_set_later_processing(void);

float core_get_variable_value(int index);
void core_set_variable_value_int(int index, uint8_t target, int value);
void core_set_variable_value(int index, uint8_t target, float value);
void core_set_later_variable_value(int index, float value, int duration);

void core_transmit_ow_values(int ow_index);
void core_request_ow_values(uint8_t *rom);
void core_periodic_processing(void);
