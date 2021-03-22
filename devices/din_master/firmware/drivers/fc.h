/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>

#define FC_CODE 0xf1

typedef struct _fc_data {
    uint8_t f1;
    uint8_t f2;
    uint8_t f3;
    uint8_t f4;
} fc_data_t;

uint8_t fc_get_data(uint8_t *rom, fc_data_t *data);
void fc_set_data(uint8_t *rom, fc_data_t *data);
