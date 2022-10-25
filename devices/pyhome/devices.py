"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

from pyb import LED
from ds18b20 import DS18B20
from dht11 import DHT11
from fc import FC
from hs import HS
from mq7 import MQ7
from pc import PC
from pyboard import Pyboard

driverList = []
deviceList = []

DATE_TIME = 0

def set_device_drivers(ow, pyhome_rom):
    """
    Creates a list of device drivers and assigns them to devices.
    """
    global driverList
    for dev in deviceList:
        dev.curr_pyhome_rom = pyhome_rom
        if dev.pyhome_rom == pyhome_rom:
            driver = False
            if dev.rom:
                driver = _find_driver_at_rom(dev.rom)

            if driver == False:
                if dev.rom == '':
                    driver = False
                elif dev.rom == 'variable':
                    driver = False
                elif dev.rom == 'pyb':
                    driver = Pyboard()
                else:                    
                    if dev.rom[0] == 0x28:
                        driver = DS18B20(ow, dev.rom)
                    elif dev.rom[0] == 0xf0:
                        driver = HS(ow, dev.rom)
                    elif dev.rom[0] == 0xf1:
                        driver = FC(ow, dev.rom)
                    elif dev.rom[0] == 0xf2:
                        driver = PC(ow, dev.rom)
                    elif dev.rom[0] == 0xf3:
                        driver = DHT11(ow, dev.rom)
                    elif dev.rom[0] == 0xf4:
                        driver = MQ7(ow, dev.rom)
                    elif dev.rom[0] == 0xf5:
                        driver = False

                driverList += [driver]
                
            dev.driver = driver

            if dev.rom == 'pyb':
                dev.driver.declare_channel(dev.channel)


def _find_driver_at_rom(rom):
    """
    Looks for a driver by unique OneWire network key. If it finds it, it returns 
    an instance of that driver.
    """
    if rom == 'variable':
        return False
    
    for driver in driverList:
        if driver and driver.rom == rom:
            return driver
    return False

def check_driver_value(rom):
    """
    Looks for a driver by a unique OneWire network key and if it finds it gives 
    a command to get device data. Next, passes the received data to the device 
    associated with the device.
    """
    driver = _find_driver_at_rom(rom)
    if driver:
        value = driver.value()
        for dev in deviceList:
            try:
                if dev.driver == driver:
                    dev._set_driver_value(value)
            except:
                pass

def get_sync_change_devices():
    """
    Returns a list ofdevices for synchronization over the RS485 network. 
    Values are selected only those that have changed since the last synchronization.
    """
    res = []
    for dev in deviceList:
        if dev.isChange:
            res += [[dev.id, dev.val]]
            dev.isChange = False
    return res

def set_sync_change_devices(data):
    global DATE_TIME
    
    for dev in data:
        if dev[0] == -100:
            if DATE_TIME != dev[1]:
                DATE_TIME = dev[1]
                try:
                    check_delays()
                except:
                    pass
        else:
            for vl in deviceList:
                if vl.id == dev[0]:
                    try:
                        vl.value(dev[1], changeFlag=False)
                    except:
                        pass

def check_delays():
    """
    The function is called whenever a time change signal arrives. 
    When called, performs a check on all deferred devices and, 
    if the execution time has expired, assigns the deferred value 
    to the device.
    """
    global DATE_TIME
    
    for dl in deviceList:
        if dl.delayTime:
            if dl.delayTime <= DATE_TIME:
                dl.value(vl.delayValue)

class Device(object):
    """
    Device class. Contains the necessary set of properties and methods 
    for servicing the internal mechanisms of the controller. 
    The main purpose of this class is to abstract away from the low level 
    of composite elements, providing the developer with a universal API 
    for managing any element of the system.
    """
    def __init__(self, id, pyhome_rom, rom, channel):
        global deviceList
        deviceList += [self] # Registering a Device in the Global List
        self.curr_pyhome_rom = False
        self.id = id
        self.rom = rom
        if type(self.rom) == list:
            self.rom = bytearray(self.rom)
        self.pyhome_rom = pyhome_rom
        self.channel = channel
        self.val = False
        self.isChange = False
        self.changeScripts = []
        self.delayTime = False
        self.delayValue = None

    def _set_driver_value(self, value):
        if self.channel:
            self.value(value[self.channel])
        else:
            self.value(value)

    def value(self, val=None, delay=0, changeFlag=True):
        """
        Method for setting/getting data to a device.
        Parameters:
           val - the value of the device to set. If None, then the method 
                 will return the current value of the device.
           delay - time in seconds for which the change of the device is delayed.
                   If the method is called with this parameter greater than 0, then 
                   changes to the device will be delayed until the specified time. 
                   Calling the method again with this parameter will update the 
                   runtime value. If the value is 0, then the previous delays will 
                   be canceled before they expire and the new value will be assigned 
                   immediately.
           changeFlag - The value that is set to the device if there was a value 
                        change. Default is True.
        """
        if val == None:
            return self.val
        else:
            # If the change is delayed, then we will not execute immediately, but 
            # indicate the change time and the desired new status to the device. 
            # Retrying to change the value will update the time or change the 
            # status immediately.
            if delay > 0:
                global DATE_TIME
                self.delayValue = val
                self.delayTime = DATE_TIME + delay
                return
            else:
                self.delayTime = False
            
            # We make sure that the device belongs to the current controller 
            # or is a system device
            if self.val != val:
                self.val = val
                try:
                    if self.driver:
                        self.driver.value(val, self.channel)
                except:
                    pass
                self.isChange = changeFlag
                if self.pyhome_rom == self.curr_pyhome_rom:
                    for script in self.changeScripts:
                        try:
                            script()
                        except:
                            LED(1).on()

    def load_value(self):
        b = self.isChange
        return (b, self.val)

    def set_change_script(self, script):
        self.changeScripts += [script]
    
