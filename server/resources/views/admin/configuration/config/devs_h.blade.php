#include <avr/pgmspace.h>

typedef struct _variable_t {
	int id;
	unsigned char ctrl_id;
	unsigned char typ;
	unsigned char direction;
	int ow_index;
	unsigned char channel;
} variable_t;
    
extern const uint8_t ow_roms[] PROGMEM;
extern const variable_t variables[] PROGMEM;
extern int variable_count;
extern float variable_values[];