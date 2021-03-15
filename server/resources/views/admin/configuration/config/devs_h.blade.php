// ---------------------------------------
//
//     Этот файл создан автоматически
//
// ---------------------------------------


#include <avr/pgmspace.h>

typedef struct _variable_t {
	int id;
	unsigned char controller_id;
	unsigned char typ;
	unsigned char direction;
	int ow_index;
	unsigned char channel;
} variable_t;
    
extern const int onewire_roms_count;
extern const uint8_t onewire_roms[] PROGMEM;
extern const variable_t variables[] PROGMEM;
extern const int variable_count;
extern float variable_values[];


int devs_get_variable_index(int id);
uint8_t devs_get_varible(int index, variable_t *variable);
int devs_get_variable_ow_index(int index);
uint8_t devs_get_variable_controller(int index);
void devs_get_ow_rom(int ow_index, uint8_t *rom);
void devs_get_variable_rom(int index, uint8_t *rom);
int devs_onewire_rom_index(uint8_t *rom);
uint8_t devs_find_variables_by_ow_index(int ow_index, int *vars);
void devs_get_variable_rom(int index, uint8_t *rom);