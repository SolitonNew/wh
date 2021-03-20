/*
 * led.h
 *
 * Created: 10.03.2021 17:44:21
 *  Author: User
 */ 

#define CONTROL_LED_R_DDR  DDRB
#define CONTROL_LED_R_PORT PORTB
#define CONTROL_LED_R_BIT  5

#define CONTROL_LED_G_DDR  DDRB
#define CONTROL_LED_G_PORT PORTB
#define CONTROL_LED_G_BIT  3

#define CONTROL_LED_Y_DDR  DDRD
#define CONTROL_LED_Y_PORT PORTD
#define CONTROL_LED_Y_BIT  4

#define CONTROL_LED_B_DDR  DDRD
#define CONTROL_LED_B_PORT PORTD
#define CONTROL_LED_B_BIT  2

#define CONTROL_BTN_1_DDR  DDRB
#define CONTROL_BTN_1_PORT PORTB
#define CONTROL_BTN_1_PIN  PINB
#define CONTROL_BTN_1_BIT  6

#define CONTROL_BTN_2_DDR  DDRB
#define CONTROL_BTN_2_PORT PORTB
#define CONTROL_BTN_2_PIN  PINB
#define CONTROL_BTN_2_BIT  4

#define CONTROL_BTN_3_DDR  DDRD
#define CONTROL_BTN_3_PORT PORTD
#define CONTROL_BTN_3_PIN  PIND
#define CONTROL_BTN_3_BIT  5

#define CONTROL_BTN_4_DDR  DDRD
#define CONTROL_BTN_4_PORT PORTD
#define CONTROL_BTN_4_PIN  PIND
#define CONTROL_BTN_4_BIT  3

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