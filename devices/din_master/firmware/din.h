/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#if MMCU == MMCU_ATMEGA16A

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

#elif MMCU == MMCU_ATMEGA328

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

#endif

void din_init(void);
void din_set_value(uint8_t channel, uint8_t value);
