"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

import pyb
from pyb import delay
from pyb import Timer
from rs485 import RS485
from onewire import OneWire
from ds18b20 import DS18B20
import utils
try:
    import config
except:
    pyb.LED(4).on()
import devices
import commands

IS_START = True

PACK_SYNC = 1
PACK_COMMAND = 2
PACK_ERROR = 3

# Bus Initialization
ow = OneWire('Y12')
DS18B20(ow).start_measure()
rs485 = RS485(3, 'Y11', pyhome_rom=1)

# We create drivers for the OneWire network devices and pass an OW instance to them.
devices.set_device_drivers(ow, rs485.pyhome_rom)

# We select a separate list of thermometers
termometrs = []
for driver in devices.driverList:
    if driver and driver.rom and (driver.rom[0] == 0x28):
        termometrs += [driver]

# We create a special timer for synchronizing thermometer updates.
timer_1_flag = False
def timer_1_handler(timer):
    global timer_1_flag
    timer_1_flag = True
Timer(1, freq=0.1).callback(timer_1_handler)

# We create a special timer to synchronize the OneWire alarms search poll.
timer_2_flag = False
def timer_2_handler(timer):
    global timer_2_flag
    timer_2_flag = True
Timer(2, freq=500).callback(timer_2_handler)

# We create a special timer for delayed changes
timer_4_flag = False
def timer_4_handler(timer):
    global timer_4_flag
    timer_4_flag = True
Timer(4, freq=1).callback(timer_4_handler)

def onewire_alarms():
    global timer_2_flag
    if timer_2_flag == False:
        return
    timer_2_flag = False
    alarms = ow.alarm_search()
    for a in alarms:
        if a[0] == 0x28: # If suddenly a thermometer with non-standard settings gets into the network
            ds = DS18B20(ow)
            ds.set_config(a, 125, -55, 12)
            ds.save_config()
        else:
            devices.check_driver_value(a)

curr_termometr_index = -1
def onewire_termometrs():
    global timer_1_flag
    
    if timer_1_flag == False:
        return
    timer_1_flag = False
    
    global curr_termometr_index

    if termometrs:
        curr_termometr_index += 1
        if curr_termometr_index > (len(termometrs) - 1):
            curr_termometr_index = 0
        devices.check_driver_value(termometrs[curr_termometr_index].rom)

def delayed_changes():
    global timer_4_flag
    if timer_4_flag == False:
        return
    timer_4_flag = False

    for dl in devices.deviceList:
        if dl.delayTime != None:
            if dl.delayTime > 0:
                dl.delayTime -= 1
            else:
                dl.value(dl.delayValue)

def swch():
    pyb.LED(1).off()
    pyb.LED(4).off()

switch = pyb.Switch().callback(swch)

read_config_file = False

while True:
    for pack in rs485.check_lan():
        if pack:
            if pack[1] == PACK_SYNC:
                read_config_file = False
                if IS_START:
                    rs485.send_pack(PACK_SYNC, "RESET")
                    IS_START = False
                else:
                    devices.set_sync_change_devices(pack[2])
                    devicesData = devices.get_sync_change_devices()
                    rs485.send_pack(PACK_SYNC, [devicesData, commands.commandList])
                    commands.commandList = []
            elif pack[1] == PACK_COMMAND:
                read_config_file = False
                comm_data = pack[2]
                if comm_data[0] == "SCAN_ONE_WIRE":
                    pyb.LED(3).on()
                    rs485.send_pack(PACK_COMMAND, [comm_data[0], False])
                    ow.search()
                elif comm_data[0] == "LOAD_ONE_WIRE_ROMS":
                    roms = []
                    for rom in ow.roms:
                        rr = []
                        for r in rom:
                            rr += [r]
                        roms += [rr]
                    rs485.send_pack(PACK_COMMAND, [comm_data[0], roms])
                    pyb.LED(3).off()
                elif comm_data[0] == "SET_CONFIG_FILE":
                    read_config_file = True
                    rs485.send_pack(PACK_COMMAND, [comm_data[0], rs485.file_parts_i])
                elif comm_data[0] == "REBOOT_CONTROLLER":
                    rs485.send_pack(PACK_COMMAND, [comm_data[0], False])
                    pyb.hard_reset()
            elif pack[1] == PACK_ERROR:
                rs485.send_pack(PACK_ERROR, [rs485.error])
                rs485.error = []
    
    if not read_config_file:
        onewire_alarms()
        onewire_termometrs()
        delayed_changes()
    
