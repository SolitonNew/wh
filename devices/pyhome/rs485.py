from pyb import UART
from pyb import Pin
from pyb import LED
from ujson import loads, dumps
from os import remove

class RS485(object):   
    def __init__(self, uart_num, pin_rw, pyhome_rom):
        self.error = []
        self.uart = UART(uart_num)
        self.uart.init(57600, bits=8, parity=None, stop=2, timeout=10, read_buf_len=64)
        self.pin_rw = Pin(pin_rw)
        self.pin_rw.init(Pin.OUT_PP)
        self.pin_rw.value(0)
        self.pyhome_rom = pyhome_rom
        
        self.file_parts = 0
        self.file_parts_i = 1
        self.file_is_open = False

    def check_lan(self):
        res = []
        uart = self.uart
        try:
            buf = uart.readall()            
            if buf:
                buf = buf.decode("utf-8")
                LED(2).toggle()
                for pack in buf.split(chr(0x0)):
                    if pack:
                        try:
                            data = False
                            data = loads(pack)
                            if len(data) > 0 and data[0] == self.pyhome_rom:
                                if data[2][0] == "SET_CONFIG_FILE":
                                    res = [data]
                                    if data[2][2] == False:
                                        self.file_parts = data[2][1]
                                        self.file_parts_i = 1
                                        self._write_config(True, '')
                                        self.file_is_open = True
                                    else:
                                        if self.file_is_open:
                                            if self.file_parts_i == data[2][1]:
                                                self._write_config(False, data[2][2])
                                                if self.file_parts_i == self.file_parts:
                                                    self.file_is_open = False
                                                self.file_parts_i += 1
                                            else:
                                                res = [[self.pyhome_rom, 3]]
                                                self.error += ["Error 3  %s" % (data)]
                                                self.file_is_open = False
                                                break
                                        else:
                                            res = [[self.pyhome_rom, 3]]
                                            self.error += ["Error 4 DATA: %s" % (data)]
                                            break
                                else:
                                    self.file_is_open = False
                                    res = [data]
                        except Exception as e:
                            res = [[self.pyhome_rom, 3]]
                            if data:
                                self.error += ["Error 1 {}".format(e.args) + " DATA:  %s" % (data)]
                            else:
                                self.error += ["Error 1 {}".format(e.args) + " PACK:  %s" % (pack)]
                            LED(4).on()
        except Exception as e:
            res = [[self.pyhome_rom, 3]]
            self.error += ["Error 2 {}".format(e.args)]
            LED(4).on()
        return res

    def send_pack(self, pack_type, pack_data):
        pin_rw = self.pin_rw.value
        uart = self.uart
        pin_rw(1)
        try:
            buf = [self.pyhome_rom, pack_type, pack_data]
            data = dumps(buf).encode("utf-8")
            data += bytearray([0x0])
            uart.write(data)
        except:
            LED(3).on()
        pin_rw(0)

    def _write_config(self, is_start, data):
        if is_start:
            f = open("config.py", "w")
        else:
            f = open("config.py", "a")
        try:
            f.write(data)
        except:
            pass
        f.close()
    
