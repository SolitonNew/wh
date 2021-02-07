#!/usr/bin/python3
#-*- coding: utf-8 -*-

from db_connector import DBConnector
import serial
import datetime
import time
import json
from config_utils import generate_config_file
import math

class Main():
    SERIAL_PORT = "/dev/ttyUSB0"
    SERIAL_SPEED = 57600

    PACK_SYNC = 1
    PACK_COMMAND = 2
    PACK_ERROR = 3

    def __init__(self):
        self.fast_timeput = 0.1 #0.05
        self.check_lan_error = False
        
        # Connect to serial port
        try:
            self.serialPort = serial.Serial(self.SERIAL_PORT, self.SERIAL_SPEED, parity='O', timeout=self.fast_timeput)
        except:
            print("Ошибка подключения к '%s'" % self.SERIAL_PORT)

        self.db = DBConnector()
        self.db.load_controllers()
        self.queue = []

        # Run main loop
        self.run()

    def send_pack(self, dev_id, pack_type, pack_data, flush=True):
        buf = json.dumps([dev_id, pack_type, pack_data]).encode("utf-8")
        buf += bytearray([0x0])

        c = 5
        for err in range(c):
            self.serialPort.write(buf)
            if flush:
                self.serialPort.flush()
            #print(buf)
            res = self.check_lan()
            if self.check_lan_error == False:
                break
            if err < (c - 1):
                self.check_lan_error = False
        return res

    def _store_variable_to_db(self, dev_id, pack_data):
        for var in pack_data:
            if len(var) > 1:
                self.db.set_variable_value(var[0], var[1], dev_id)

    def check_lan(self):
        try:
            buf = self.serialPort.readline()
            if len(buf) > 0:
                #print(buf)
                resp = buf.decode("utf-8")
                data = []
                for pack in resp.split(chr(0x0)):
                    if len(pack) > 0:
                        d = json.loads(pack)
                        data += [d]
                        if d[1] == self.PACK_ERROR:
                            self.check_lan_error = True
                            for s in d[2]:
                                self._command_info("%s" % s)
                return data
            else:
                return False
        except Exception as e:
            self.check_lan_error = True
            self._command_info("EXCEPT {}".format(e.args))
            return False

    def _sync_variables(self):
        # Зачитываем изменения в БД
        var_data = self.db.variable_changes()
        recv_valid = False

        # Шлем посылку никому, чтобы контроллеры приготовились принимать
        self.send_pack(0, self.PACK_SYNC, [])
        time.sleep(0.02)
        # Рассылаем изменения в БД и паралельно читаем обновления
        for dev in self.db.controllers:
            for rep in range(3): # 3 попытки отослать пакет
                pack_data = []
                lt = time.localtime()
                t = time.mktime((2000, 1, 1, 0, 0, 0, 0, 0, lt.tm_isdst))
                pack_data += [[-100, round(time.time() - t)]] #Передаем системное время в контроллеры
                for var in var_data:
                    if var[2] != dev[0]:
                        pack_data += [[var[0], var[1]]]

                date = datetime.datetime.now().strftime('%H:%M:%S')
                print("[%s] SYNC. '%s': " % (date, dev[1]), end="")
                cl = self.send_pack(dev[0], self.PACK_SYNC, pack_data)
                if cl:
                    for res_pack in cl:
                        if res_pack[2] == "RESET":
                            print("RESET ", end="")
                            is_ok = self.send_pack(dev[0], self.PACK_SYNC, self._reset_pack()) != False
                            for r in range(30):
                                if self.check_lan():
                                    is_ok = True
                            if is_ok:
                                print("OK\n")
                                recv_valid = True
                            else:
                                print("ERROR\n")
                        else:
                            self._store_variable_to_db(res_pack[0], res_pack[2])
                            print("OK")
                            print("   >> ", pack_data)
                            print("   << ", res_pack[2], "\n")
                            recv_valid = True
                else:
                    print("ERROR\n")

                time.sleep(0.02)
                
                if recv_valid: # Обмен прошел успешно повторы не требуются
                    break

    def _reset_pack(self):
        return self.db.all_variables();

    def _command_info(self, text, replace_text = None):
        text = text.replace("'", "`")
        text = text.replace('"', '\"')
        s = self.db.get_property('RS485_COMMAND_INFO')
        if replace_text == None:
            text = text.replace("<", "&lt;")
            text = text.replace(">", "&gt;")
            print(text)
            self.db.set_property('RS485_COMMAND_INFO', s + '<p>' + text + '</p>')
        else:
            self.db.set_property('RS485_COMMAND_INFO', s.replace(text, replace_text))

    def _send_commands(self):
        command = self.db.get_property('RS485_COMMAND')

        if command == "":
            return

        self.db.set_property('RS485_COMMAND_INFO', '')
        
        for dev in self.db.controllers:            
            error_text = "Контроллер '%s' не ответил." % dev[1]

            if command == "SCAN_OW":
                self._command_info("Запрос поиска OneWire устройств для контроллера '%s'..." % dev[1])
                if self.send_pack(dev[0], self.PACK_COMMAND, ["SCAN_ONE_WIRE", ""]):
                    self._command_info("Пауза 3с...")
                    time.sleep(3)
                    self._command_info("Запрос списка найденых на шине OneWire устройств для контроллера '%s'" % dev[1])
                    is_ok = False
                    for res_pack in self.send_pack(dev[0], self.PACK_COMMAND, ["LOAD_ONE_WIRE_ROMS", ""]):
                        count = 0
                        allCount = len(res_pack[2][1])
                        is_ok = True
                        for rom in res_pack[2][1]:
                            rom_s = []
                            for r in rom:
                                ss = hex(r).upper()
                                if len(ss) == 3:
                                    ss = ss.replace("0X", "0x0")
                                else:
                                    ss = ss.replace("0X", "0x")
                                rom_s += [ss]
                                rom_s += [", "]
                            self._command_info("".join(rom_s[:-1]))
                            if self.db.append_scan_rom(dev[0], rom):
                                count += 1
                        self._command_info("Всего найдено устройств: %s. Новых: %s" % (allCount, count))
                        
                    if is_ok == False:
                        self._command_info(error_text)
                else:
                    self._command_info(error_text)
            elif command == "CONFIG_UPDATE":
                self.serialPort.timeout = 2
                time.sleep(0.1)
                try:
                    self._command_info("CONFIG FILE UPLOAD '%s'..." % dev[1])
                    #pack_data = self._str_to_hex(generate_config_file(self.db))
                    pack_data = generate_config_file(self.db)
                    self._command_info(str(len(pack_data)) + ' bytes.')

                    #bts = 512
                    #bts = 128
                    bts = 1024
                    cou = math.ceil(len(pack_data) / bts)
                    is_ok = False
                    c_pack = self.send_pack(dev[0], self.PACK_COMMAND, ["SET_CONFIG_FILE", cou, False], False)
                    if c_pack and self.check_lan_error == False:
                        prev_command = "Начало загрузки..."
                        self._command_info(prev_command)
                        for i in range(cou):
                            t = i * bts
                            s = pack_data[t:t + bts]
                            c_pack = self.send_pack(dev[0], self.PACK_COMMAND, ["SET_CONFIG_FILE", i + 1, s], i == cou - 1)
                            if c_pack != False and self.check_lan_error or (i == cou - 1):
                                is_ok = True
                                if i != cou - 1:
                                    #Значит не долили файл
                                    self._command_info("ВНИМАНИЕ: Загрузка прервана");
                                break
                            else:
                                new_command = self._gen_text_progress(i, cou)
                                self._command_info(prev_command, new_command)
                                prev_command = new_command                                
                        self._command_info(prev_command, self._gen_text_progress(cou, cou))
                        
                    if is_ok:
                        self._command_info("OK")
                    else:
                        self._command_info(error_text)
                except:
                    pass
                self.serialPort.timeout = self.fast_timeput
                self.check_lan_error = False
            elif command == "REBOOT_CONTROLLERS":
                self.serialPort.timeout = 1
                self._command_info("Запрос перезагрузки контроллера '%s'..." % dev[1])
                if self.send_pack(dev[0], self.PACK_COMMAND, ["REBOOT_CONTROLLER", ""]):
                    self._command_info("OK")
                else:
                    self._command_info(error_text)
                self.serialPort.timeout = self.fast_timeput
            elif command == "GET_OW_VALUES":
                pass
                
        self.db.set_property('RS485_COMMAND', '')
        self._command_info("Готово.")
        time.sleep(2)
        self._command_info("TERMINAL EXIT")

    def _gen_text_progress(self, pos, max_pos):
        i = round(pos * 100 / max_pos)
        s = "<center>["
        s += ("<span style=\"color:#ffffff;\">|</span>") * (i + 1)
        s += ("<span style=\"color:#000000;\">|</span>") * (100 - i - 1)
        s += "] " + str(i) + "% </center>"
        return s

    """
    def _str_to_hex(self, text):
        res = []
        for c in text:
            s = hex(ord(c)).replace('0x', '')
            if len(s) == 1:
                s = '0' + s
            res += [s]
        return "".join(res)
    """

    SYNC_STATE = ""
            
    def run(self):
        serialPort = self.serialPort        
        while True:
            # Синхронизируем переменные между сервером и контроллерами
            SYNC_STATE = self.db.get_property('SYNC_STATE')

            stateChange = SYNC_STATE != self.SYNC_STATE
            if SYNC_STATE == "RUN":
                if stateChange:
                    print("Синхронизация запущена")
                try:
                    self._sync_variables()
                except:
                    pass
            else:
                if stateChange:
                    print("Синхронизация остановлена")
                time.sleep(0.1)

            self.SYNC_STATE = SYNC_STATE

            # Рассылаем системные комманды, если требуется
            self._send_commands()

print(
"=============================================================================\n"
"                  МОДУЛЬ ВЗАИМОДЕЙСТВИЯ ПО ШИНЕ RS485 v0.1\n"
"\n"
" Порт: %s \n"
" Скорость: %s \n"
"=============================================================================\n"
% (Main.SERIAL_PORT, Main.SERIAL_SPEED)
)

if __name__ == "__main__":
    Main()
