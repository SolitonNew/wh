/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8

/*
    Team package. Can be sent both ways.
    
    sign: "CMD"
    controller_id: ID of the controller with which the exchange is performed.
                   Other controllers, seeing packets with a different ID, ignore them.
    cmd: 1 - reset                 controller reboot
         2 - match receive         controller get ready to receive VAR data (number in tag field)
                                   (if not initialized it ignores the data)
         3 - match transmit        controller prepare to give VAR data
         4 - pack transmit count   the packet is transmitted immediately after transmit by the controller (number of records
                                   transfers in the tag field)
         5 - pack transmit init    can give the controller after transmit if not initialized (request
                                   initialization)
         6 - match receive init    the controller must prepare initialization packets (number in the tag field)
         7 - match ow scan         request packet to the controller so that it scans its network (by
                                   readiness sends the pack transmit count packet and further ROM packets).
        24 - firmware              Request to start the firmware on the bootloader. After it, we just start sending
                                   HEX firmware packages. In the tag field, the number of packets to receive.
        25 - firmware query size   The number of HEX packets that were received by the controller is requested.
                                   The tag contains the number of packets that were sent.
                                   If the numbers match, then the controller after sending cmd: 4 goes
                                   to the main program
        26 - query firmware        Manual firmware request.
        27 - from boot             Response controller package to cmd: 3 from the bootloader. This could be
                                   if the controller is overloaded during normal operation.
    tag: Some number that can be sent in a packet (depending on the situation)
    crc: Checksum with an algorithm similar to onewire.
    
    Note: Any valid packet processed by the controller but not addressed to it will reset the rs485_is_online flag
*/
typedef struct _rs485_cmd_pack {  // 8 bytes
    uint8_t sign[3];  // CMD
    uint8_t controller_id;
    uint8_t cmd;
    int tag;
    uint8_t crc;
} rs485_cmd_pack_t;

/*
    Packet of transferring integer value.
    
    sign: "int"
    controller_id: ID of the controller with which the exchange is performed.
                   Other controllers seeing packets with someone else's ID ignore it.
    data: the value of the integer number
    crc: Checksum with algorithm similar to onewire.
*/
typedef struct _rs485_int_pack {  // 7 bytes
    uint8_t sign[3];  // INT
    uint8_t controller_id;
    int data;
    uint8_t crc;
} rs485_int_pack_t;

/*
    Packet of transferring the value of ONE variable.
    
    sign: "var"
    controller_id: ID of the controller with which the exchange is performed.
                   Other controllers seeing packets with someone else's ID ignore it.
    id: ID of the variable
    value: the value of the variable
    crc: Checksum with algorithm similar to onewire.
*/
typedef struct _rs485_var_pack {  // 9 bytes
    uint8_t sign[3];  // VAR
    uint8_t controller_id;
    int id;
    int value;
    uint8_t crc;
} rs485_var_pack_t;

/*
    ONE Record ROM Transfer Package
    
    sign: ROM
    controller_id: ID of the controller with which the exchange is performed.
                   Other controllers seeing packets with someone else's ID ignore it.
    rom: ROM
    crc: Checksum with algorithm similar to onewire.
*/
typedef struct _rs485_ow_rom_pack {  // 13 bytes
    uint8_t sign[3]; // ROM
    uint8_t controller_id;
    uint8_t rom[8];
    uint8_t crc;
} rs485_ow_rom_pack_t;

/*
    Transfer package 8 bytes firmware.
    The controller in normal mode should only know about this package
    to ignore. It turns out exclusively by a bootloader.

    sign: HEX
    controller_id: ID of the controller with which the exchange is performed.
                   Other controllers seeing packets with someone else's ID ignore it.
    data: hex data
    crc: Checksum with algorithm similar to onewire.
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
void rs485_processing(void);
void rs485_transmit_CMD(uint8_t cmd, int tag);
void rs485_transmit_VAR(int id, int value);
void rs485_transmit_ROM(uint8_t *rom);
void rs485_in_buff_unpack(void);
