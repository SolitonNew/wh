/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#if MMCU == MMCU_ATMEGA16A

#define CONTROL_LED_R_DDR  DDRA
#define CONTROL_LED_R_PORT PORTA
#define CONTROL_LED_R_BIT  1

#define CONTROL_LED_G_DDR  DDRA
#define CONTROL_LED_G_PORT PORTA
#define CONTROL_LED_G_BIT  3

#define CONTROL_LED_Y_DDR  DDRA
#define CONTROL_LED_Y_PORT PORTA
#define CONTROL_LED_Y_BIT  5

#define CONTROL_LED_B_DDR  DDRA
#define CONTROL_LED_B_PORT PORTA
#define CONTROL_LED_B_BIT  7

#define CONTROL_BTN_1_DDR  DDRA
#define CONTROL_BTN_1_PORT PORTA
#define CONTROL_BTN_1_PIN  PINA
#define CONTROL_BTN_1_BIT  0

#define CONTROL_BTN_2_DDR  DDRA
#define CONTROL_BTN_2_PORT PORTA
#define CONTROL_BTN_2_PIN  PINA
#define CONTROL_BTN_2_BIT  2

#define CONTROL_BTN_3_DDR  DDRA
#define CONTROL_BTN_3_PORT PORTA
#define CONTROL_BTN_3_PIN  PINA
#define CONTROL_BTN_3_BIT  4

#define CONTROL_BTN_4_DDR  DDRA
#define CONTROL_BTN_4_PORT PORTA
#define CONTROL_BTN_4_PIN  PINA
#define CONTROL_BTN_4_BIT  6

#elif MMCU == MMCU_ATMEGA328

#define CONTROL_LED_R_DDR  DDRC
#define CONTROL_LED_R_PORT PORTC
#define CONTROL_LED_R_BIT  4

#define CONTROL_LED_G_DDR  DDRC
#define CONTROL_LED_G_PORT PORTC
#define CONTROL_LED_G_BIT  3

#define CONTROL_LED_Y_DDR  DDRB
#define CONTROL_LED_Y_PORT PORTB
#define CONTROL_LED_Y_BIT  2

#define CONTROL_LED_B_DDR  DDRB
#define CONTROL_LED_B_PORT PORTB
#define CONTROL_LED_B_BIT  3

#define CONTROL_BTN_1_DDR  DDRC
#define CONTROL_BTN_1_PORT PORTC
#define CONTROL_BTN_1_PIN  PINC
#define CONTROL_BTN_1_BIT  2

#define CONTROL_BTN_2_DDR  DDRC
#define CONTROL_BTN_2_PORT PORTC
#define CONTROL_BTN_2_PIN  PINC
#define CONTROL_BTN_2_BIT  1

#define CONTROL_BTN_3_DDR  DDRC
#define CONTROL_BTN_3_PORT PORTC
#define CONTROL_BTN_3_PIN  PINC
#define CONTROL_BTN_3_BIT  0

#define CONTROL_BTN_4_DDR  DDRB
#define CONTROL_BTN_4_PORT PORTB
#define CONTROL_BTN_4_PIN  PINB
#define CONTROL_BTN_4_BIT  4

#endif

typedef struct _control_btn_states {
    uint8_t btn_1_down;
    uint8_t btn_1_change;
    uint8_t btn_2_down;
    uint8_t btn_2_change;
    uint8_t btn_3_down;
    uint8_t btn_3_change;
    uint8_t btn_4_down;
    uint8_t btn_4_change;
} control_btn_states_t;

void control_init(void);
void control_led_r(uint8_t new_state);
void control_led_g(uint8_t new_state);
void control_led_y(uint8_t new_state);
void control_led_b(uint8_t new_state);

void control_check_btn(control_btn_states_t *states);
