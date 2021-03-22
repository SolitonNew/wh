/*
 *  Author: Moklyak Alexandr
 */ 

#include "core.h"

float command_get(int index) {
    return core_get_variable_value(index);
}

void command_set(int index, float value, ...) {
    core_set_variable_value(index, 3, value);
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
