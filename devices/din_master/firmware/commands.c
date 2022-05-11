/*
 *  Author: Moklyak Alexandr
 */ 

#include "core.h"
#include "schedule.h"
#include <math.h>
#include <stdarg.h>

float command_get(int index) {
    return core_get_variable_value(index);
}

void command_set(int index, float value) {
    core_set_variable_value(index, 3, value);
}

void command_set_later(int index, float value, int duration) {
    schedule_variable_value(index, value, duration);
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

void command_play(char args, int id, ...) {
    core_server_commands[core_server_commands_count++] = 1 | (int)args >> 8;
    core_server_commands[core_server_commands_count++] = id;
    va_list a;
    va_start(a, id);
    for (uint8_t i = 0; i < args; i++) {
        core_server_commands[core_server_commands_count++] = va_arg(a, int);
    }
    va_end(a);
}

void command_speech(char args, int id, ...) {
    core_server_commands[core_server_commands_count++] = 2 | (int)args >> 8;
    core_server_commands[core_server_commands_count++] = id;
    va_list a;
    va_start(a, id);
    for (uint8_t i = 0; i < args; i++) {
        core_server_commands[core_server_commands_count++] = va_arg(a, int);
    }
    va_end(a);
}

void command_print_i(int value) {

}

void command_print_f(float value) {

}

void command_print_s(char *text) {

}