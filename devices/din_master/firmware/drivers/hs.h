/*
 *  Author: Moklyak Alexandr
 */ 

#define HS_CODE 0xf0

typedef struct _hs_data {
    uint8_t left;
    uint8_t right;
} hs_data_t;

uint8_t hs_get_data(uint8_t *rom, hs_data_t *data);
