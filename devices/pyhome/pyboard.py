from pyb import Pin

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

    def declare_channel(self, channel):
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
