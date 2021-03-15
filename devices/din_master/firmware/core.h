/*
 * variables.h
 *
 * Created: 07.03.2021 13:34:54
 *  Author: User
 */

#include <avr/io.h>

float core_get_variable_value(int index);
void core_set_variable_value(int index, uint8_t target, float value);
void core_init(void);
void core_rs485_processing(void);
void core_onewire_alarm_processing(void);

void core_transmit_ow_values(int ow_index);
void core_request_ow_values(uint8_t *rom);
void core_schedule_processing(void);