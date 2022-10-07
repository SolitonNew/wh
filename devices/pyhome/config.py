from variables import Variable

# Variables
din_1_R1 = Variable(1, 1, 0, 'din', 'R1')
din_2_R2 = Variable(2, 1, 0, 'din', 'R2')
din_3_R3 = Variable(3, 1, 0, 'din', 'R3')
DIN_GREEN = Variable(4, 1, 0, 'din', 'R4')
ow_5_LEFT = Variable(5, 1, 0, 'ow', 'LEFT')
ow_6_RIGHT = Variable(6, 1, 0, 'ow', 'RIGHT')
orangepi_7_PA6 = Variable(7, 2, 0, 'orangepi', 'PA6')
orangepi_8_PA7 = Variable(8, 2, 0, 'orangepi', 'PA7')
orangepi_9_PA8 = Variable(9, 2, 0, 'orangepi', 'PA8')
orangepi_10_PA9 = Variable(10, 2, 0, 'orangepi', 'PA9')
orangepi_11_PA10 = Variable(11, 2, 0, 'orangepi', 'PA10')
orangepi_12_PA13 = Variable(12, 2, 0, 'orangepi', 'PA13')
orangepi_13_PA14 = Variable(13, 2, 0, 'orangepi', 'PA14')
orangepi_14_PA18 = Variable(14, 2, 0, 'orangepi', 'PA18')
orangepi_15_PA19 = Variable(15, 2, 0, 'orangepi', 'PA19')
orangepi_16_PA20 = Variable(16, 2, 0, 'orangepi', 'PA20')
orangepi_17_PA21 = Variable(17, 2, 0, 'orangepi', 'PA21')
orangepi_18_PC4 = Variable(18, 2, 0, 'orangepi', 'PC4')
orangepi_19_PC7 = Variable(19, 2, 0, 'orangepi', 'PC7')
orangepi_20_PD14 = Variable(20, 2, 0, 'orangepi', 'PD14')
ORANGE_LED_RED = Variable(21, 2, 0, 'orangepi', 'PG6')
ORANGE_LED_GREEN = Variable(22, 2, 0, 'orangepi', 'PG7')
orangepi_23_PG8 = Variable(23, 2, 0, 'orangepi', 'PG8')
orangepi_24_PG9 = Variable(24, 2, 0, 'orangepi', 'PG9')
orangepi_25_PROC_TEMP = Variable(25, 2, 0, 'orangepi', 'PROC_TEMP')
i2c_26_TEMP = Variable(26, 2, 0, 'i2c', 'TEMP')
i2c_27_P = Variable(27, 2, 0, 'i2c', 'P')
extapi_28_TEMP = Variable(28, 3, 0, 'extapi', 'TEMP')
extapi_29_P = Variable(29, 3, 0, 'extapi', 'P')
extapi_30_CC = Variable(30, 3, 0, 'extapi', 'CC')
extapi_31_G = Variable(31, 3, 0, 'extapi', 'G')
extapi_32_H = Variable(32, 3, 0, 'extapi', 'H')
extapi_33_V = Variable(33, 3, 0, 'extapi', 'V')
extapi_34_WD = Variable(34, 3, 0, 'extapi', 'WD')
extapi_35_WS = Variable(35, 3, 0, 'extapi', 'WS')
extapi_36_MP = Variable(36, 3, 0, 'extapi', 'MP')
DIN_TEMP_2 = Variable(37, 1, 0, 'ow', 'TEMP')
DIN_TEMP_! = Variable(38, 1, 0, 'ow', 'TEMP')
ow_41_LEFT_LONG = Variable(41, 1, 0, 'ow', 'LEFT_LONG')
ow_42_RIGHT_LONG = Variable(42, 1, 0, 'ow', 'RIGHT_LONG')
cam_43_REC = Variable(43, 4, 0, 'camcorder', 'REC')
cam_44_REC = Variable(44, 4, 0, 'camcorder', 'REC')
cam_45_REC = Variable(45, 4, 0, 'camcorder', 'REC')
cam_46_REC = Variable(46, 4, 0, 'camcorder', 'REC')

# Scripts
def script_49():
    pass

# Links
LIVING_S.set_change_script(script_1)
