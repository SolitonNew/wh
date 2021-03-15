/*
 * ow4rele.h
 *
 * Created: 10.03.2021 22:44:05
 *  Author: User
 */ 

#include <avr/io.h>

typedef struct _fc_data {
	uint8_t f1;
	uint8_t f2;
	uint8_t f3;
	uint8_t f4;
} fc_data_t;

uint8_t fc_get_data(uint8_t *rom, fc_data_t *data);
void fc_set_data(uint8_t *rom, fc_data_t *data);