/*
 *  Author: Moklyak Alexandr
 */ 

#define HS_CODE 0xf0
#define HS_READ_DATA 0xA0
#define HS_BUTTON_LEFT 8
#define HS_BUTTON_RIGHT 16
#define HS_BUTTON_LONG_LEFT 32
#define HS_BUTTON_LONG_RIGHT 64

typedef struct _hs_data {
    uint8_t left;
    uint8_t right;
} hs_data_t;

uint8_t hs_get_data(uint8_t *rom, hs_data_t *data);
