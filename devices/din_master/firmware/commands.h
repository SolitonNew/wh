/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

float command_get(int index);
void command_set(int index, float value);
void command_set_later(int index, float value, int delay);
void command_toggle(int index);
void command_toggle_later(int index, int delay);
void command_on(int index);
void command_on_later(int index, int delay);
void command_off(int index);
void command_off_later(int index, int delay);
void command_info(void);
void command_play(char args, int id, ...);
void command_speech(char args, int id, ...);
void command_print_i(int value);
void command_print_f(float value);
void command_print_s(char *text);
int command_abs_i(int value);
float command_abs_f(float value);
int command_round(float value);
int command_ceil(float value);
int command_floor(float value);