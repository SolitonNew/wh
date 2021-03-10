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

int get_variable_index(int id) {
	for (int i = 0; i < variable_count; i++) {
		int vid = pgm_read_dword(&variables[i]);
		if (vid == id) {
			return i;
		}
	}
	
	return -1;
}

float get_variable_value(int index) {
	return 0;
}

void set_variable_value(int index, float val) {	
	
	
}
