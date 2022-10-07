from pyb import LED
import drivers

driverList = []
variableList = []

DATE_TIME = 0

def set_variable_drivers(ow, dev_id):
    """
    Creates a list of device drivers and assigns them to variables.
    """
    global driverList
    for var in variableList:
        var.curr_dev_id = dev_id
        if var.dev_id == dev_id:
            driver = False
            if var.rom:
                driver = _find_driver_at_rom(var.rom)

            if driver == False:
                if var.rom == '':
                    driver = False
                elif var.rom == 'variable':
                    driver = False
                elif var.rom == 'pyb':
                    driver = drivers.Pyboard()
                else:                    
                    if var.rom[0] == 0x28:
                        driver = drivers.Termometr(ow, var.rom)
                    elif var.rom[0] == 0xf0:
                        driver = drivers.Switch(ow, var.rom)
                    elif var.rom[0] == 0xf1:
                        driver = drivers.Fan(ow, var.rom)
                    elif var.rom[0] == 0xf2:
                        driver = drivers.Pins(ow, var.rom)
                    elif var.rom[0] == 0xf3:
                        driver = drivers.Dht11(ow, var.rom)
                    elif var.rom[0] == 0xf4:
                        driver = drivers.Mq7(ow, var.rom)
                    elif var.rom[0] == 0xf5:
                        driver = drivers.Amp(ow, var.rom)

                driverList += [driver]
                
            var.driver = driver

            if var.rom == 'pyb':
                var.driver.declare_channel(var.channel, var.direction)


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
    a command to get device data. Next, passes the received data to the variable 
    associated with the device.
    """
    driver = _find_driver_at_rom(rom)
    if driver:
        value = driver.value()
        for var in variableList:
            try:
                if var.driver == driver:
                    var._set_driver_value(value)
            except:
                pass

def get_sync_change_variables():
    """
    Returns a list of variables for synchronization over the RS485 network. 
    Values are selected only those that have changed since the last synchronization.
    """
    res = []
    for var in variableList:
        if var.isChange:
            res += [[var.id, var.val]]
            var.isChange = False
    return res

def set_sync_change_variables(data):
    global DATE_TIME
    
    for var in data:
        for vl in variableList:
            if vl.id == var[0]:
                """
                try:
                    if vl.dev_id == 100:
                        vl.system_value(var[1])
                        if vl.id == -100:
                            if DATE_TIME != vl.value():
                                DATE_TIME = vl.value()
                                check_delays()
                    else:
                        vl.silent_value(var[1])
                except:
                    pass
                """
                try:
                    vl.value(var[1], changeFlag=False)
                    if vl.id == -100:
                        if DATE_TIME != vl.value():
                            DATE_TIME = vl.value()
                            check_delays()
                except:
                    pass

def check_delays():
    """
    The function is called whenever a time change signal arrives. 
    When called, performs a check on all deferred variables and, 
    if the execution time has expired, assigns the deferred value 
    to the variable.
    """
    global DATE_TIME
    
    for vl in variableList:
        if vl.delayTime:
            if vl.delayTime <= DATE_TIME:
                vl.value(vl.delayValue)

class Variable(object):
    """
    Variable class. Contains the necessary set of properties and methods 
    for servicing the internal mechanisms of the controller. 
    The main purpose of this class is to abstract away from the low level 
    of composite elements, providing the developer with a universal API 
    for managing any element of the system.
    """
    def __init__(self, id, dev_id, direction, rom, channel):
        global variableList
        variableList += [self] # Registering a Variable in the Global List
        self.curr_dev_id = False
        self.id = id
        self.rom = rom
        if type(self.rom) == list:
            self.rom = bytearray(self.rom)
        self.dev_id = dev_id
        self.direction = direction
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

    """
    def silent_value(self, val):
        if val == None:
            return
        self.val = val
        try:
            self.driver.value(val, self.channel)
        except:
            pass

    def system_value(self, val):
        if val == None:
            return
        if self.val != val:
            self.val = val
            if self.changeScript:
                self.changeScript()
    """

    def value(self, val=None, delay=0, changeFlag=True):
        """
        Method for setting/getting data to a variable.
        Parameters:
           val - the value of the variable to set. If None, then the method 
                 will return the current value of the variable.
           delay - time in seconds for which the change of the variable is delayed.
                   If the method is called with this parameter greater than 0, then 
                   changes to the variable will be delayed until the specified time. 
                   Calling the method again with this parameter will update the 
                   runtime value. If the value is 0, then the previous delays will 
                   be canceled before they expire and the new value will be assigned 
                   immediately.
           changeFlag - The value that is set to the variable if there was a value 
                        change. Default is True.
        """
        if val == None:
            return self.val
        else:
            # If the change is delayed, then we will not execute immediately, but 
            # indicate the change time and the desired new status to the variable. 
            # Retrying to change the value will update the time or change the 
            # status immediately.
            if delay > 0:
                global DATE_TIME
                self.delayValue = val
                self.delayTime = DATE_TIME + delay
                return
            else:
                self.delayTime = False
            
            # We make sure that the variable belongs to the current controller 
            # or is a system variable
            """
            if self.dev_id == self.curr_dev_id or self.dev_id == 100:
                if self.val != val:
                    self.val = val
                    if self.driver:
                        self.driver.value(val, self.channel)
                    self.isChange = True
                    if self.changeScript:
                        self.changeScript()
            """
            if self.val != val:
                self.val = val
                try:
                    if self.driver:
                        self.driver.value(val, self.channel)
                except:
                    pass
                self.isChange = changeFlag
                if self.dev_id == self.curr_dev_id or self.dev_id == 100:
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
