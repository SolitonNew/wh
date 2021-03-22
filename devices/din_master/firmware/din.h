/*
 *  Author: Moklyak Alexandr
 */ 

#define DIN_R1_DDR  DDRC
#define DIN_R1_PORT PORTC
#define DIN_R1_BIT  0

#define DIN_R2_DDR  DDRC
#define DIN_R2_PORT PORTC
#define DIN_R2_BIT  1

#define DIN_R3_DDR  DDRC
#define DIN_R3_PORT PORTC
#define DIN_R3_BIT  2

#define DIN_R4_DDR  DDRC
#define DIN_R4_PORT PORTC
#define DIN_R4_BIT  3

void din_init(void);
void din_set_value(uint8_t channel, uint8_t value);
