/*
 * ds18b20.h
 *
 * Created: 07.03.2021 20:03:18
 *  Author: User
 */ 

#define ONEWIRE_CONVERTTEMP 0x44
#define ONEWIRE_RSCRATCHPAD 0xBE

void ds18b20_start_measure(unsigned char *rom);
float ds18b20_get_value(unsigned char *rom);
void ds18b20_set_value(unsigned char *rom, float val);