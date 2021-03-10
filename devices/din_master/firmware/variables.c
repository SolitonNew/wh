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

int get_variable_index(int id) {
	uint8_t size = sizeof(variable_t);
	for (int i = 0; i < variable_count; i++) {
		int vid = pgm_read_dword(&variables[i]);
		if (vid == id) {
			return i;
		}
	}
	
	return -1;
}

float get_variable_value(int index) {

}

void set_variable_value(int index, float val) {	
	
	
}