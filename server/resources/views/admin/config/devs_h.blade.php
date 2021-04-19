// ---------------------------------------
//
//     Этот файл создан автоматически
//
// ---------------------------------------


#include <avr/pgmspace.h>
    
#define ONEWIRE_ROMS_COUNT {{ count($owList) }}
#define ONEWIRE_ROMS_SIZE ONEWIRE_ROMS_COUNT * 8
#define VARIABLE_COUNT {{ count($varList) }}

typedef struct _variable_t {
    int id;
    unsigned char controller_id;
    unsigned char typ;
    int ow_index;
    unsigned char channel;
} variable_t;

extern const uint8_t onewire_roms[ONEWIRE_ROMS_SIZE] PROGMEM;
extern const variable_t variables[VARIABLE_COUNT] PROGMEM;
extern int variable_values[VARIABLE_COUNT];


int devs_get_variable_index(int id);
uint8_t devs_get_varible(int index, variable_t *variable);
int devs_get_variable_ow_index(int index);
uint8_t devs_get_variable_controller(int index);
void devs_get_ow_rom(int ow_index, uint8_t *rom);
void devs_get_variable_rom(int index, uint8_t *rom);
int devs_onewire_rom_index(uint8_t *rom);
uint8_t devs_find_variables_by_ow_index(int ow_index, int *vars);
void devs_get_variable_rom(int index, uint8_t *rom);