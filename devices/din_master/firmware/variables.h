/*
 * variables.h
 *
 * Created: 07.03.2021 13:34:54
 *  Author: User
 */

#include "config/devs.h"

int get_variable_index(int id);
uint8_t get_variable_controller(int index);
uint8_t get_variable_typ(int index);
uint8_t get_variable_direction(int index);
int get_variable_ow_index(int index);
uint8_t get_variable_channel(int index);

float get_variable_value(int index);
void set_variable_value(int index, float value);