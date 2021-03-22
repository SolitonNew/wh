/*
 *  Author: Moklyak Alexandr
 */ 

#define DS18B20_CODE 0x28
#define ONEWIRE_CONVERTTEMP 0x44
#define ONEWIRE_RSCRATCHPAD 0xBE

typedef struct _ds18b20_data {
    float temp;
} ds18b20_data_t;

void ds18b20_start_measure(uint8_t *rom);
uint8_t ds18b20_get_data(uint8_t *rom, ds18b20_data_t *data);
