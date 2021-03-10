/*
 * commands.h
 *
 * Created: 09.03.2021 23:30:55
 *  Author: User
 */ 

float command_get(int index);
void command_set(int index, float value, ...);
void command_toggle(int index);
void command_on(int index, ...);
void command_off(int index, ...);
void command_info(void);
void command_play(char *file);
void command_speech(char *text);