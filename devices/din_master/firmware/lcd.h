/*
 * log.h
 *
 * Created: 09.03.2021 9:39:44
 *  Author: User
 */ 

#define LCD_DDR DDRC
#define LCD_PORT PORTC
#define LCD_RST 3
#define LCD_SCK 2
#define LCD_DC 1
#define LCD_DATA 0

void lcd_init(void);
void lcd_char(uint8_t c);
void lcd_text(uint8_t *text, uint8_t num);
void lcd_hex(uint8_t byte);
void lcd_clear(void);
void lcd_move(uint8_t x, uint8_t y);
void lcd_nl(void);