/*
 * variables.c
 *
 * Created: 07.03.2021 13:34:41
 *  Author: User
 */ 

#include <avr/io.h>
#include <avr/pgmspace.h>
#include "config/devs.h"
#include "config/scripts.h"

int variable_count;
float variable_values[];

int get_variable_index(int id) {
	for (int i = 0; i < variable_count; i++) {
		int vid = pgm_read_dword(&variables[i]);
		if (vid == id) {		
			return i;
		}
	}
	return -1;
}

uint8_t get_variable_controller(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 2);
}

uint8_t get_variable_typ(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 3);
}

uint8_t get_variable_direction(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 4);
}

int get_variable_ow_index(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_dword((int)(&variables[index]) + 5);
}

uint8_t get_variable_channel(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 7);
}

float get_variable_value(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return variable_values[index];
}

void set_variable_value(int index, float value) {
	if ((index < 0) || (index >= variable_count)) return ;
	variable_values[index] = value;
}
