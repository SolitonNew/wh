/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>

#define CORE_VARIABLE_CHANGED_COUNT_MAX 30
#define CORE_SERVER_COMMANDS_SIZE_MAX 24

extern int core_variable_changed[CORE_VARIABLE_CHANGED_COUNT_MAX];
extern uint8_t core_variable_changed_count;

extern int core_server_commands[CORE_SERVER_COMMANDS_SIZE_MAX];
extern uint8_t core_server_commands_count;

void core_init(void);
void core_onewire_alarm_processing(void);
float core_get_variable_value(int index);
void core_set_variable_value_int(int index, uint8_t target, int value);
void core_set_variable_value(int index, uint8_t target, float value);
void core_transmit_ow_values(int ow_index);
void core_request_ow_values(uint8_t *rom);
