#include <avr/pgmspace.h>

typedef struct variable {
    int id;
    unsigned char controller;
    unsigned char typ;       // 0-pyb;1-ow;2-variable
    unsigned char direction; // 0, 1
    char name[24];
    int ow_index;            // Порядковый номер в массиве ow_roms
    unsigned char channel;   // порядковый номе канала
}

const unsigned char ow_roms[264] PROGMEM = {
    0xF0, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x1A,
    0xF0, 0x00, 0x00, 0x00, 0x00, 0x00, 0x03, 0xA6,
    0x28, 0xDF, 0x76, 0x75, 0x05, 0x00, 0x00, 0x13,
    0xF1, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x27,
    0xF2, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x60,
    0x28, 0x9B, 0xF3, 0x75, 0x05, 0x00, 0x00, 0xA3,
    0x28, 0x70, 0x17, 0x75, 0x05, 0x00, 0x00, 0xB7,
    0xF3, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x5D,
    0xF3, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0xBF,
    0xF3, 0x00, 0x00, 0x00, 0x00, 0x00, 0x03, 0xE1,
    0xF4, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0xEE,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x01, 0xB1,
    0xF0, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0xF8,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x03, 0x0D,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x02, 0x53,
    0x28, 0xD7, 0x69, 0x76, 0x05, 0x00, 0x00, 0x29,
    0xF0, 0x00, 0x00, 0x00, 0x00, 0x00, 0x03, 0xA6,
    0xF0, 0x00, 0x00, 0x00, 0x00, 0x00, 0x05, 0x7B,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x04, 0x8E,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x05, 0xD0,
    0xF0, 0x00, 0x00, 0x00, 0x01, 0x00, 0x06, 0x32,
    0xF2, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x82,
    0xF2, 0x00, 0x00, 0x00, 0x00, 0x00, 0x03, 0xDC,
    0x28, 0x86, 0x91, 0x75, 0x05, 0x00, 0x00, 0x20,
    0xF1, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0xC5,
    0xF2, 0x00, 0x00, 0x00, 0x00, 0x00, 0x04, 0x5F,
    0x28, 0xFF, 0xC7, 0x9C, 0xC1, 0x16, 0x04, 0x17,
    0x28, 0xFF, 0xB1, 0x97, 0xB5, 0x16, 0x03, 0x80,
    0x28, 0xFF, 0xA1, 0x6C, 0xC1, 0x16, 0x04, 0xEB,
    0x28, 0x29, 0xC9, 0x75, 0x05, 0x00, 0x00, 0xF3,
    0x28, 0x5E, 0x3E, 0x75, 0x05, 0x00, 0x00, 0x19,
    0xF2, 0x00, 0x00, 0x00, 0x00, 0x00, 0x05, 0x01,
    0x28, 0xFF, 0x32, 0xA8, 0xB5, 0x16, 0x03, 0xD7,
};

const struct variable variables[144] PROGMEM = {
    -100, 100, 2, 0, -1, 0,
    1, 1, 1, 0, 9, 0,
    2, 1, 1, 0, 6, 0,
    3, 1, 1, 0, 0, 0,
    5, 1, 1, 0, 1, 0,
    6, 1, 1, 0, 1, 0,
    7, 2, 1, 0, 14, 0,
    8, 2, 1, 0, 14, 0,
    9, 2, 1, 0, -1, 0,
    10, 2, 1, 0, -1, 0,
    11, 2, 1, 0, -1, 0,
    13, 2, 1, 0, 16, 0,
    14, 2, 1, 0, 16, 0,
    17, 1, 1, 0, 12, 0,
    18, 1, 1, 0, 12, 0,
    19, 2, 1, 0, 20, 0,
    21, 1, 1, 0, 11, 0,
    22, 2, 1, 0, 13, 0,
    23, 1, 0, 1, -1, 0,
    24, 1, 0, 1, -1, 0,
    25, 1, 0, 1, -1, 0,
    26, 1, 0, 1, -1, 0,
    27, 2, 0, 1, -1, 0,
    28, 2, 0, 1, -1, 0,
    29, 2, 0, 1, -1, 0,
    30, 2, 0, 1, -1, 0,
    31, 2, 0, 1, -1, 0,
    32, 2, 0, 1, -1, 0,
    33, 2, 0, 1, -1, 0,
    34, 2, 0, 1, -1, 0,
    35, 2, 0, 1, -1, 0,
    36, 2, 0, 1, -1, 0,
    37, 1, 0, 1, -1, 0,
    38, 1, 0, 1, -1, 0,
    39, 2, 0, 1, -1, 0,
    40, 2, 0, 1, -1, 0,
    41, 1, 0, 1, -1, 0,
    42, 2, 0, 1, -1, 0,
    44, 2, 2, 1, -1, 0,
    45, 2, 1, 1, -1, 0,
    47, 2, 2, 1, -1, 0,
    48, 2, 1, 1, -1, 0,
    49, 1, 1, 0, 26, 0,
    50, 1, 2, 1, -1, 0,
    51, 1, 1, 1, -1, 0,
    56, 2, 1, 0, 29, 0,
    57, 2, 2, 1, -1, 0,
    58, 2, 1, 1, -1, 0,
    59, 1, 1, 0, 2, 0,
    60, 1, 2, 1, -1, 0,
    61, 1, 1, 0, -1, 0,
    62, 1, 1, 0, -1, 0,
    63, 1, 2, 1, -1, 0,
    64, 1, 1, 0, -1, 0,
    65, 2, 1, 0, -1, 0,
    66, 2, 2, 1, -1, 0,
    67, 2, 1, 0, -1, 0,
    68, 2, 1, 0, -1, 0,
    69, 2, 2, 1, -1, 0,
    70, 2, 1, 1, -1, 0,
    75, 1, 2, 0, -1, 0,
    82, 1, 0, 1, -1, 0,
    89, 1, 0, 1, -1, 0,
    90, 1, 1, 0, 0, 0,
    91, 1, 1, 0, 30, 0,
    93, 1, 1, 0, 27, 0,
    95, 1, 1, 0, 28, 0,
    100, 1, 2, 1, -1, 0,
    103, 1, 1, 1, 3, 0,
    104, 1, 1, 1, 3, 0,
    105, 1, 1, 1, 3, 0,
    106, 1, 1, 1, -1, 0,
    107, 1, 1, 1, 3, 0,
    108, 2, 1, 1, 24, 0,
    109, 2, 1, 1, -1, 0,
    110, 2, 1, 1, -1, 0,
    111, 2, 1, 0, 21, 0,
    112, 2, 1, 0, 22, 0,
    113, 2, 1, 0, 22, 0,
    114, 1, 1, 0, -1, 0,
    115, 1, 0, 1, -1, 0,
    116, 1, 2, 1, -1, 0,
    123, 100, 2, 1, -1, 0,
    124, 1, 1, 0, 5, 0,
    125, 1, 1, 0, 4, 0,
    126, 100, 2, 1, -1, 0,
    127, 1, 1, 0, 4, 0,
    130, 1, 1, 0, 7, 0,
    131, 1, 1, 0, 7, 0,
    133, 1, 1, 0, 8, 0,
    134, 1, 1, 0, 9, 0,
    135, 1, 1, 0, 10, 0,
    136, 1, 1, 0, 11, 0,
    137, 2, 1, 0, 18, 0,
    138, 2, 1, 0, -1, 0,
    139, 2, 1, 0, -1, 0,
    145, 2, 1, 0, 21, 0,
    146, 2, 1, 0, 22, 0,
    147, 2, 1, 0, 22, 0,
    148, 2, 2, 0, -1, 0,
    149, 2, 2, 0, -1, 0,
    150, 100, 2, 1, -1, 0,
    151, 100, 2, 1, -1, 0,
    152, 1, 1, 0, 23, 0,
    153, 2, 1, 0, -1, 0,
    154, 2, 1, 0, 25, 0,
    155, 2, 1, 1, -1, 0,
    156, 2, 1, 1, -1, 0,
    157, 2, 1, 1, -1, 0,
    158, 2, 1, 0, -1, 0,
    159, 2, 2, 1, -1, 0,
    161, 2, 1, 0, -1, 0,
    162, 2, 1, 0, -1, 0,
    163, 2, 1, 0, 31, 0,
    164, 2, 1, 0, 31, 0,
    165, 2, 2, 0, -1, 0,
    166, 100, 2, 1, -1, 0,
    167, 100, 2, 1, -1, 0,
    168, 100, 2, 1, -1, 0,
    169, 100, 2, 1, -1, 0,
    182, 2, 1, 0, 13, 0,
    184, 2, 1, 0, 20, 0,
    185, 2, 1, 0, 24, 0,
    186, 2, 1, 0, 24, 0,
    187, 2, 1, 0, 24, 0,
    189, 1, 1, 0, 4, 0,
    190, 1, 1, 0, 4, 0,
    191, 2, 1, 0, 21, 0,
    192, 2, 1, 0, 21, 0,
    193, 2, 1, 0, 25, 0,
    194, 2, 1, 0, 25, 0,
    195, 2, 1, 0, 25, 0,
    201, 2, 2, 1, -1, 0,
    268, 2, 1, 0, 15, 0,
    270, 2, 1, 0, 32, 0,
    272, 2, 1, 0, 17, 0,
    273, 2, 1, 0, 17, 0,
    274, 2, 1, 0, 18, 0,
    275, 2, 1, 0, 19, 0,
    276, 2, 1, 0, 19, 0,
    279, 1, 1, 0, 8, 0,
    280, 2, 1, 0, 31, 0,
    281, 2, 1, 0, 31, 0,
    282, 2, 1, 0, -1, 0,
};

float variablesValue[144];

void script_1(void) {
if (command_get(3) == 1) {
    command_toggle(18);
} else
if (command_get(3) == 2) {
    command_set(18, command_get(18));
    command_set(20, command_get(18));
    command_set(19, command_get(18));
    command_set(32, command_get(18));
    command_set(33, command_get(18));
}}

void script_2(void) {
if (command_get(63)) {
    command_toggle(19);
}}

void script_3(void) {
if (command_get(4)) {
    command_toggle(20);
}

command_set(4, 1);

if (command_get(4) > 2) {
    command_play('TRACK');
}

if (command_get(4) > 3) {
    command_info();
}

for ( int i = 0; i < 10; i++) {
    command_toggle(4);
}}

void script_4(void) {
if (command_get(16)) {
    command_toggle(36);
}}

void script_21(void) {
if (command_get(15)) {
    command_toggle(34);
    
    if (command_get(34) == 0) {
        command_set(99, 1);
        command_set(99, 0, 6);
    }
}}

void script_22(void) {
if (command_get('SHOWER_2_S')) {
    command_toggle(35);
}}

void script_23(void) {
if (command_get(17)) {
    command_toggle(37);
}}

void script_24(void) {
if (command_get(13)) {
    command_toggle(32);
}}

void script_25(void) {
}

void script_26(void) {
if (command_get(14)) {
    command_toggle(33);
}}

void script_27(void) {
}

void script_28(void) {
if (command_get(11)) {
    command_toggle(28);
}}

void script_29(void) {
if (command_get('BEDROOM_2_MAIN_S')) {
    command_toggle(30);
}}

void script_30(void) {
if (command_get(5)) {
    command_toggle(21);
}}

void script_31(void) {
if (command_get(6)) {
    command_toggle(22);
    
    if (command_get(22) == 0) {
        command_set(115, 1);
        command_set(115, 0, 10);
    }
}}

void script_32(void) {
if (command_get(7)) {
    command_toggle(23);
}}

void script_33(void) {
if (command_get(8)) {
    command_toggle(24);
}}

void script_34(void) {
if (command_get(9)) {
    command_toggle(25);
}

command_set(26, command_get(25));}

void script_35(void) {
if (command_get(10)) {
    command_toggle(26);
}}

void script_36(void) {
if (command_get('WC_1_S')) {
    command_toggle(27);
    
    if (command_get(27) == 0) {
        command_set(100, 1);
        command_set(100, 0, 6);
    }
}}

void script_39(void) {
if (command_get('BEDROOM_2_SECOND_S')) {
    command_toggle(31);
}}

void script_40(void) {
if (command_get(12)) {
    command_toggle(29);
}}

void script_43(void) {
 int fan_min_val = 0;

if (command_get(34) && command_get(97)) {
    command_set(69, 6, 180);
} else {
    command_set(69, fan_min_val, 180);
}}

void script_44(void) {
 int fan_min_val = 0;

if (command_get(27) && command_get(96)) {
    command_set(68, 6, 180);
} else {
    command_set(68, fan_min_val, 180);
}}

void script_45(void) {
 int c = 2;

if (command_get(68) > 2) {
    c += 2;
}

if (command_get(69) > 2) {
    c += 2;
}

if (command_get(71) > 2) {
    c += 2;
}

if (command_get(70) > 2) {
    c += 2;
}

if (c > 4) {
    command_set(72, c);
} else {
    command_set(72, c, 2);
}}

void script_46(void) {
 int fan_min_val = 0;

if (command_get(36)) {
    command_set(71, 10, 120);
} else {
    command_set(71, fan_min_val, 180);
}}

void script_47(void) {
 int fan_min_val = 0;

}

void script_48(void) {
if (command_get(100) == 0) {
    if (command_get(76)) {
        command_on(27);
    } else {
        command_off(27, 180);
    }
}}

void script_49(void) {
 int c = 0;}

void script_51(void) {
if (command_get(84) || command_get(86)) {
    if (!command_get(85) || command_get(26)) {
        command_on(20);
    }
} else {
    command_off(20, 200);
}}

void script_52(void) {
if (command_get(93) == 2) {
    command_toggle(62);
}}

void script_54(void) {
if (command_get(99) == 0) {
    if (command_get(77)) {
        command_on(34);
    } else {
        command_off(34, 180);
    }
}}

void script_55(void) {
if (command_get(105)) {
    if (command_get(73) > 1) {
        command_set(73, 0);
    } else
    if (command_get(73) == 0) {
        command_set(73, 1);
    } else {
        command_set(73, 10);
    }
}}

void script_56(void) {
if (command_get(115) == 0) {
    if (command_get(114)) {
        if (!command_get(85)) {
            command_on(22);
        }
    } else {
        command_off(22, 200);
    }
}}

void script_57(void) {
 int c = 1;}


