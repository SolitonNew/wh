/*
 * mq7.h
 *
 * Created: 10.03.2021 22:28:43
 *  Author: User
 */ 

#include <avr/io.h>

#define MQ7_CODE 0xf4

typedef struct _mq7_data {
	float co;
} mq7_data_t;

uint8_t mq7_get_data(uint8_t *rom, mq7_data_t *data);