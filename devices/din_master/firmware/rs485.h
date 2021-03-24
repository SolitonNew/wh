/*
 *  Author: Moklyak Alexandr
 */ 

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8
//#define USART_RXC_vect _VECTOR(12) // 11

/*
    Пакет команды. Может быть отправлен в обе стороны.
    
    sign: "CMD"
    controller_id: ИД контроллера с которым выполняется обмен.
                   Другие контролеры видя пакеты с чужим ИД игнорируют их.
    cmd: 1 - reset                 перезагрузка контроллера
         2 - match receive         контроллеру приготовиться получать данные VAR (кол-во в поле tag) 
                                   (если не инициализирован то игнорирует данные)
         3 - match transmit        контроллеру приготовиться отдавать данные VAR
         4 - pack transmit count   пакет передается сразу после transmit уже контроллером (кол-во записей 
                                   передачи в поле tag)
         5 - pack transmit init    может отдать контроллер после transmit если не инициализирован (запрос 
                                   инициализации)
         6 - match receive init    контроллер должен приготовиться пакеты инициализации (кол-во в поле tag)
         7 - match ow scan         пакет запроса к контроллеру, что бы он просканировал свою сеть (по 
                                   готовности отдает пакет pack transmit count и дальше пакеты ROM).
        24 - firmware              Запрос начала прошивки по бутлоадеру. После него начинаем просто слать 
		                           пакеты прошивки HEX. В поле tag кол-во пакетов для получения.
		25 - firmware query size   Запрашивается кол-во пакетов HEX которые были получены контроллером. 
                                   В tag передается кол-во пакетов, что были отправлены. 
                                   Если числа совпадают, то контроллер после отправки cmd: 4 переходит 
                                   к основной программе
        26 - query firmware        Запрос прошивки в ручном режиме.
        27 - from boot             Ответный пакет контроллера на cmd: 3 из бутлоадера. Такое может быть 
                                   если контроллер перегрузили во время штатной работы.
    tag: Некое число, которое может быть передано в пакете (в зависимости от ситуации)
    crc: Контрольная сума с алгоритмом аналогичным onewire.
    
    Примечание: Любой валидный пакет обработаный контроллером но не адресуемый ему сбрасывает флаг rs485_is_online
*/
typedef struct _rs485_cmd_pack {  // 8 bytes
    uint8_t sign[3];  // CMD
    uint8_t controller_id;
    uint8_t cmd;
    int tag;
    uint8_t crc;
} rs485_cmd_pack_t;

/*
    Пакет передачи значения ОДНОЙ переменной.
    
    sign: "VAR"
    controller_id: ИД контроллера с которым выполняется обмен.
                   Другие контролеры видя пакеты с чужим ИД игнорируют его.
    id: ИД переменной
    value: значение переменной
    crc: Контрольная сума с алгоритмом аналогичным onewire.
*/
typedef struct _rs485_var_pack {  // 9 bytes
    uint8_t sign[3];  // VAR
    uint8_t controller_id;
    int id;
    int value;
    uint8_t crc;
} rs485_var_pack_t;

/*
    Пакет передачи ОДНОЙ записи ROM
    
    sign: ROM
    controller_id: ИД контроллера с которым выполняется обмен.
                   Другие контролеры видя пакеты с чужим ИД игнорируют его.
    rom: ROM
    crc: Контрольная сума с алгоритмом аналогичным onewire.
*/
typedef struct _rs485_ow_rom_pack {  // 13 bytes
    uint8_t sign[3]; // ROM
    uint8_t controller_id;
    uint8_t rom[8];
    uint8_t crc;
} rs485_ow_rom_pack_t;

/*
    Пакет передачи 8 байт прошивки.
	Контроллер в штатном режиме должен знать про этот пакет только 
	что бы игнорировать. Получается исключительно бутлоадером.
	
	sign: HEX
	controller_id: ИД контроллера с которым выполняется обмен.
                   Другие контролеры видя пакеты с чужим ИД игнорируют его.
    data: hex данные
	crc: Контрольная сума с алгоритмом аналогичным onewire.
*/
typedef struct _rs485_hex_pack { // 13 bytes
	uint8_t sign[3]; // HEX
	uint8_t controller_id;
	uint8_t data[8];
	uint8_t crc;
} rs485_hex_pack_t;

extern uint16_t rs485_errors;
extern uint16_t rs485_packs;
extern uint16_t rs485_recieve_count;

void rs485_init(void);
void rs485_transmit_CMD(uint8_t cmd, int tag);
void rs485_transmit_VAR(int id, int value);
void rs485_transmit_ROM(uint8_t *rom);
void rs485_in_buff_unpack(void);
