/*
 * variables.h
 *
 * Created: 07.03.2021 13:34:54
 *  Author: User
 */

typedef struct _variable_t {
	int id;
	unsigned char ctrl_id;
	unsigned char typ;
	unsigned char direction;
	int ow_index;
	unsigned char channel;
} variable_t;

float command_get(int index);
void command_set(int index, float value, ...);
void command_toggle(int index);
void command_on(int index, ...);
void command_off(int index, ...);