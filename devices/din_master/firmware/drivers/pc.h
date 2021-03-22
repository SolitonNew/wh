/*
 *  Author: Moklyak Alexandr
 */ 

#include <avr/io.h>

#define PC_CODE 0xf2

typedef struct _pc_data {
    uint8_t p1;
    uint8_t p2;
    uint8_t p3;
    uint8_t p4;
} pc_data_t;

uint8_t pc_get_data(uint8_t *rom, pc_data_t *data);
