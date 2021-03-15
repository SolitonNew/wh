/*
 * din.h
 *
 * Created: 15.03.2021 12:45:53
 *  Author: User
 */ 

#define DIN_R1_DDR  DDRD
#define DIN_R1_PORT PORTD
#define DIN_R1_BIT  3

#define DIN_R2_DDR  DDRD
#define DIN_R2_PORT PORTD
#define DIN_R2_BIT  4

#define DIN_R3_DDR  DDRB
#define DIN_R3_PORT PORTB
#define DIN_R3_BIT  0

#define DIN_R4_DDR  DDRB
#define DIN_R4_PORT PORTB
#define DIN_R4_BIT  1

void din_init(void);
void din_set_value(uint8_t channel, uint8_t value);