/*
 * ow4rele.h
 *
 * Created: 10.03.2021 22:44:05
 *  Author: User
 */ 

#include <avr/io.h>

typedef struct _ow4rele_data {
	uint8_t f1;
	uint8_t f2;
	uint8_t f3;
	uint8_t f4;
} ow4rele_data_t;

uint8_t ow4rele_get_data(uint8_t *rom, ow4rele_data_t *data);
void ow4rele_set_data(uint8_t *rom, ow4rele_data_t *data);