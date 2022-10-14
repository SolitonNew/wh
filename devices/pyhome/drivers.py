from ds18b20 import DS18B20
from homesensor import HomeSensor
from fancontrol import FanControl
from pincontrol import PinControl
from dht11 import DHT11
from mq7 import MQ7
from ampmetr import Ampmetr
from tc_new import TcNew
from pyb import Pin

class Termometr(DS18B20):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
        self.is_started = False
    
    def value(self, val = None, channel = ''):
        if val == None:
            if self.is_started:
                res = self.get_temp(self.rom)
                if res:
                    res = ((res * 10)//1)/10
            else:
                res = None
            self.start_measure()
            self.is_started = True
            if res == 85:
                return None
            return res
        else:
            return False
            

class Switch(HomeSensor):
    SENSOR_L = 8
    SENSOR_R = 16
    SENSOR_LONG_L = 32
    SENSOR_LONG_R = 64
        
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
    
    def value(self, val = None, channel = ''):
        if val == None:
            resL = 0
            resR = 0
            
            d = self.get_data(self.rom)

            if d != None:
                if (d & self.SENSOR_L):
                    if (d & self.SENSOR_LONG_L):
                        resL = 2
                    else:
                        resL = 1

                if (d & self.SENSOR_R):
                    if (d & self.SENSOR_LONG_R):
                        resR = 2
                    else:
                        resR = 1

            return {'LEFT':resL, 'RIGHT':resR}

class Fan(FanControl):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
        self.data = bytearray(4)
    
    def value(self, val = None, channel = ''):
        if val == None:
            d = self.get_data(self.rom)

            if d != None:
                self.data = bytearray(d)

            return {'F1':self.data[0], 'F2':self.data[1], 'F3':self.data[2], 'F4':self.data[3]}
        else:
            if channel == 'F1':
                self.data[0] = int(val)
            elif channel == 'F2':
                self.data[1] = int(val)
            elif channel == 'F3':
                self.data[2] = int(val)
            elif channel == 'F4':
                self.data[3] = int(val)
            
            self.set_data(self.data, self.rom)

class Pins(PinControl):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
        self.data = bytearray(4)

    def value(self, val = None, channel = ''):
        if val == None:
            d = self.get_data(self.rom)

            if d:
                self.data = d
                return {'P1':self.data[0], 'P2':self.data[1], 'P3':self.data[2], 'P4':self.data[3]}
            
            return None

class Dht11(DHT11):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
    
    def value(self, val = None, channel = ''):
        if val == None:
            resH = 0
            resT = 0
            
            d = self.get_data(self.rom)

            if d != None:
                resH = d[0]
                resT = d[1]
            return {'H':resH, 'T':resT}

class Mq7(MQ7):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
    
    def value(self, val = None, channel = ''):
        if val == None:
            res = self.get_data(self.rom)
            if res:
                res = ((res * 10)//1)/10
            return res

class Amp(Ampmetr):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
    
    def value(self, val = None, channel = ''):
        if val == None:
            res = self.get_data(self.rom)
            if res:
                res = ((res * 2.5)//1)/2.5
                res = ((res * 10)//1)/10
            return res

class Pyboard(object):
    def __init__(self):
        self.available_pins = ('X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'X8',
                               'X9', 'X10', 'X11', 'X12',
                               'Y1', 'Y2', 'Y3', 'Y4', 'Y5', 'Y6', 'Y7', 'Y8')
        self.rom = 'pyb'
        self.channels = []

    def _find_channel(self, channel):
        for c in self.channels:
            if c.names()[1] == channel:
                return c
        return False

    def declare_channel(self, channel, direction):
        try:
            self.available_pins.index(channel)
            if self._find_channel(channel):
                return ;
            pin = Pin(channel)
            if direction:
                pin.init(Pin.OUT_PP)
            else:
                pin.init(Pin.IN, Pin.PULL_UP)
            self.channels += [pin]
        except:
            pass

    def value(self, val = None, channel = ''):
        pin = self._find_channel(channel)
        if pin:
            if val == None:
                return pin.value()
            else:
                pin.value(val)
        else:
            return False

class TermoControl(TcNew):
    def __init__(self, ow, rom):
        super().__init__(ow)
        self.rom = rom
        self.is_started = False
    
    def value(self, val = None, channel = ''):
        if val == None:
            res = self.get_data(self.rom)
            try:
                return ({'T1':res[0][0], 'T2':res[0][1], 'T3':res[0][2], 'T4':res[0][3], 'T5':res[0][4],
                         'R1':res[1][0], 'R2':res[1][1], 'R3':res[1][2], 'S1':res[1][3], 'S2':res[1][4], 'S3':res[1][5]})
            except:
                return False
        else:
            return False
