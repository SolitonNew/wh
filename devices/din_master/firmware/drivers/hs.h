/*
 *  Author: Moklyak Alexandr
 */ 

#define HS_CODE 0xf0
#define HS_BUTTON_LEFT 8
#define HS_BUTTON_RIGHT 16
#define HS_BUTTON_LEFT_LONG 32
#define HS_BUTTON_RIGHT_LONG 64

typedef struct _hs_data {
    uint8_t left;
    uint8_t right;
    uint8_t left_long;
    uint8_t right_long;
} hs_data_t;

uint8_t hs_get_data(uint8_t *rom, hs_data_t *data);
