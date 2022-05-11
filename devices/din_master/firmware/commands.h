/*
 *  Author: Moklyak Alexandr
 */ 

float command_get(int index);
void command_set(int index, float value);
void command_set_later(int index, float value, int duration);
void command_toggle(int index);
void command_toggle_later(int index, int duration);
void command_on(int index);
void command_on_later(int index, int duration);
void command_off(int index);
void command_off_later(int index, int duration);
void command_info(void);
void command_play(char args, int id, ...);
void command_speech(char args, int id, ...);
void command_print_i(int value);
void command_print_f(float value);
void command_print_s(char *text);
