/*
 * commands.c
 *
 * Created: 09.03.2021 23:31:37
 *  Author: User
 */ 

#include "variables.h"

float command_get(int index) {
	return get_variable_value(index);
}

void command_set(int index, float value, ...) {
	set_variable_value(index, value);
}

void command_toggle(int index) {
	if (command_get(index)) {
		command_set(index, 0);
	} else {
		command_set(index, 1);
	}
}

void command_on(int index, ...) {
	command_set(index, 1);
}

void command_off(int index, ...) {
	command_set(index, 0);
}

void command_info(void) {
	
}

void command_play(char *file) {
	
}

void command_speech(char *text) {
	
}