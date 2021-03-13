#include <avr/pgmspace.h>

typedef struct _variable_t {
	int id;
	unsigned char ctrl_id;
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