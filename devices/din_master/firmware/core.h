/*
 * variables.h
 *
 * Created: 07.03.2021 13:34:54
 *  Author: User
 */

#include <avr/io.h>

int core_get_variable_index(int id);
uint8_t core_get_variable_controller(int index);
uint8_t core_get_variable_typ(int index);
uint8_t core_get_variable_direction(int index);
int core_get_variable_ow_index(int index);
uint8_t core_get_variable_channel(int index);
void core_get_variable_rom(int index, uint8_t *rom);
int core_onewire_rom_index(uint8_t *rom);
uint8_t core_find_variables_by_ow_index(int ow_index, int *vars);
void core_get_variable_rom(int index, uint8_t *rom);
float core_get_variable_value(int index);
void core_set_variable_value(int index, float value);
void core_init(void);
void core_rs485_processing(void);
void core_onewire_alarm_processing(void);
void core_schedule_processing(void);