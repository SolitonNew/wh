/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>

#define DHT11_CODE 0xf3

typedef struct _dht11_data {
    int h;
    int t;
} dht11_data_t;

uint8_t dht11_get_data(uint8_t *rom, dht11_data_t *data);
