/*
 *  Author: Moklyak Alexandr
 */ 

#include "core.h"

float command_get(int index) {
    return core_get_variable_value(index);
}

void command_set(int index, float value) {
    core_set_variable_value(index, 3, value);
}

void command_set_later(int index, float value, int duration) {
    core_set_later_variable_value(index, value, duration);
}

void command_toggle(int index) {
    if (command_get(index)) {
        command_set(index, 0);
    } else {
        command_set(index, 1);
    }
}

void command_toggle_later(int index, int duration) {
    if (command_get(index)) {
        command_set_later(index, 0, duration);
    } else {
        command_set_later(index, 1, duration);
    }
}

void command_on(int index) {
    command_set(index, 1);
}

void command_on_later(int index, int duration) {
	command_set_later(index, 1, duration);
}

void command_off(int index) {
    command_set(index, 0);
}

void command_off_later(int index, int duration) {
	command_set_later(index, 0, duration);
}

void command_info(void) {
    
}

void command_play(char *file) {
    
}

void command_speech(char *text) {
    
}
