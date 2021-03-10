/*
 * ds18b20.h
 *
 * Created: 07.03.2021 20:03:18
 *  Author: User
 */ 

#define ONEWIRE_CONVERTTEMP 0x44
#define ONEWIRE_RSCRATCHPAD 0xBE

typedef struct _ds18b20_data {
	float temp;
} ds18b20_data_t;

void ds18b20_start_measure(uint8_t *rom);
uint8_t ds18b20_get_data(uint8_t *rom, ds18b20_data_t *data);