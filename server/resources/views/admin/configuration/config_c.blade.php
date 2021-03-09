#include <avr/pgmspace.h>

extern const unsigned char ow_roms[] PROGMEM;
extern const variable_t variables[] PROGMEM;
extern float variable_values[];

@foreach($scriptList as $row)
void script_{{ $row->ID }}(void);
@endforeach