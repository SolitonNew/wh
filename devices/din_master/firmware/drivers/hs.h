/*
 * hs.h
 *
 * Created: 07.03.2021 22:40:15
 *  Author: User
 */ 

typedef struct _hs_data {
	uint8_t left;
	uint8_t right;
} hs_data_t;

uint8_t hs_get_data(uint8_t *rom, hs_data_t *data);