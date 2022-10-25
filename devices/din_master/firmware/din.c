/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#include "config/mmcu.h"
#include "board.h"
#include <avr/io.h>
#include "din.h"

void din_init(void) {
    CPIN(DIN_R1_PORT, DIN_R1_BIT);
    SPIN(DIN_R1_DDR, DIN_R1_BIT);
	
    CPIN(DIN_R2_PORT, DIN_R2_BIT);
    SPIN(DIN_R2_DDR, DIN_R2_BIT);
	
    CPIN(DIN_R3_PORT, DIN_R3_BIT);
    SPIN(DIN_R3_DDR, DIN_R3_BIT);
	
    CPIN(DIN_R4_PORT, DIN_R4_BIT);
    SPIN(DIN_R4_DDR, DIN_R4_BIT);
}

void din_set_value(uint8_t channel, uint8_t value) {
    switch (channel) {
        case 0: // R1
            if (value) {
                SPIN(DIN_R1_PORT, DIN_R1_BIT);
            } else {
                CPIN(DIN_R1_PORT, DIN_R1_BIT);
            }
            break;
        case 1: // R2
            if (value) {
                SPIN(DIN_R2_PORT, DIN_R2_BIT);
            } else {
                CPIN(DIN_R2_PORT, DIN_R2_BIT);
            }
            break;
        case 2: // R3
            if (value) {
                SPIN(DIN_R3_PORT, DIN_R3_BIT);
            } else {
                CPIN(DIN_R3_PORT, DIN_R3_BIT);
            }
            break;
        case 3: // R4
            if (value) {
                SPIN(DIN_R4_PORT, DIN_R4_BIT);
            } else {
                CPIN(DIN_R4_PORT, DIN_R4_BIT);
            }
            break;
    }
}
