#!/usr/bin/python3
#-*- coding: utf-8 -*-

from db_connector import DBConnector
import time
import datetime
import subprocess

try:
    from bmp280 import BMP280
except:
    pass

class Main():
    def __init__(self):
        self.db = DBConnector()
        self.termostats = []
        self._add_termostat(50, 49, "В хозяйской спальне") #Спальня №3
        self._add_termostat(47, 46, "В детской спальне") #Спальня №2
        self._add_termostat(44, 43, "В гостевой спальне") #Спальня №1
        self._add_termostat(60, 59, "В гостинной") #Гостинная
        self._add_termostat(66, 65, "В кухне") #Кухня

        # ID, CHANNEL, VALUE
        self.BMP280_VARS = [[150, "t", None], [151, "p", None]]
        self.bmp280_drv = None        
        self.bmp280_init()

        self.cam_alerts = []
        self._load_cam_alerts()
        
        self.run()

    def bmp280_init(self):
        addr = 0x76
        for i in range(2):
            addr += i
            try:
                self.bmp280_drv = BMP280(addr)
                print("BMP280 OK: %s" % (hex(addr)))
            except:
                print("BMP280 ERROR: %s" % (hex(addr)))
        
    def run(self):
        termostats_time_step = 0
        bmp280_time_step = 0
        clear_db_mem_time_ignore = False
        boiler_term = None
        while True:
            relIds = []
            relIds_named = []
            for keys in self.db.select("select CORE_GET_LAST_CHANGE_ID()"):
                if keys[0] - 1 > self.db.lastVarChangeID:
                    # c.ID, c.VARIABLE_ID, c.VALUE, v.APP_CONTROL, v.GROUP_ID
                    for row in self.db.variable_changes():
                        if row[3] == 1: # Слежение за светом
                            relIds += [str(row[1]), ","]
                        if row[3] == 3: # Слежение за розетками
                            relIds_named += [str(row[1]), ","]
                        elif row[3] == 8: # Слежение за пиродатчиками (камерами)
                            try:
                                cam_num = self.cam_alerts.index(row[1]) + 1
                                if row[2] == 1:
                                    self._add_command('speech("Замечено движение на камере %s", "notify")' % (cam_num))
                            except:
                                pass
                        elif row[3] == 4: #Термометры
                            for r in self.termostats:
                                if r[2] == row[1]:
                                    r[3] = row[2]
                            # критические температуры
                            if row[1] == 95 and row[2] > 55: # Дымоход
                                self._add_command('speech("Температура дымохода %s градусов", "alarm")' % (round(row[2])))
                            if row[1] == 93:
                                boiler_term = row[2]
                                if row[2] > 55: # Подача котла
                                    self._add_command('speech("Температура котла %s градусов", "alarm")' % (round(row[2])))
                                elif row[2] >= 45 and row[2] <= 48:
                                    self._add_command('speech("Котел холодный", "notify")')
                            # -----------------------
                        elif row[3] == 5: #Термостаты
                            for r in self.termostats:
                                if r[0] == row[1]:
                                    r[1] = row[2]
                        elif row[1] == 163 and row[2] == 1:
                            self._add_command('speech("Прозвенел звон*ок на воротах")')

                    if len(relIds) > 0:
                        for row in self.db.select("select v.APP_CONTROL, c.NAME, p.NAME, v.VALUE "
                                                  "  from core_variables v, core_variable_controls c, plan_parts p "
                                                  " where v.ID in (%s) "
                                                  "   and v.APP_CONTROL = c.ID "
                                                  "   and v.GROUP_ID = p.ID "
                                                  " order by v.ID" % ("".join(relIds[:-1]),)):
                            s = [str(row[2], "utf-8"), ". ", str(row[1], "utf-8"), " "]
                            if row[3]:
                                s += ["включен"]
                            else:
                                s += ["выключен"]
                                
                            self._add_command('speech("%s", "notify")' % "".join(s).lower())

                    if len(relIds_named) > 0:
                        for row in self.db.select("select v.COMM, v.VALUE "
                                                  "  from core_variables v "
                                                  " where v.ID in (%s) " % ("".join(relIds_named[:-1]),)):
                            comm = str(row[0], "utf-8")
                            s = [comm, " "]
                            if comm[-1::].upper() == "А":
                                if row[1]:
                                    s += ["включена"]
                                else:
                                    s += ["выключена"]
                            else:
                                if row[1]:
                                    s += ["включен"]
                                else:
                                    s += ["выключен"]
                                
                            self._add_command('speech("%s", "notify")' % "".join(s).lower())

            if termostats_time_step == 0:
                termostats_time_step = round(15 * 60 / 0.2)
                if boiler_term != None and boiler_term > 30:
                    for t in self.termostats:
                        if t[3] > t[1] + 0.2: # Перегрели
                            self._add_command('speech("%s жарко", "notify")' % (t[4]))
                        elif t[3] < t[1] - 0.2 and t[3] > t[1] - 1: # Переостудили
                            self._add_command('speech("%s холодно", "notify")' % (t[4]))
            termostats_time_step -= 1

            if bmp280_time_step == 0:
                bmp280_time_step = round(60 / 0.2)
                self._check_bmp280()
            bmp280_time_step -= 1

            # -------------------------------------------
            if datetime.datetime.now().hour == 4:
                if not clear_db_mem_time_ignore:
                    clear_db_mem_time_ignore = True
                    self.clear_mem_db()
                    self.clear_values()
            else:
                clear_db_mem_time_ignore = False
            # -------------------------------------------
            
            time.sleep(0.2)

    def _add_termostat(self, tst_id, trm_id, title):
        # Зачитка стартовых значений
        for rec in self.db.select("select VALUE from core_variables where ID = %s" % (tst_id)):
            tst_val = rec[0]
        for rec in self.db.select("select VALUE from core_variables where ID = %s" % (trm_id)):
            trm_val = rec[0]
        # ---------------------------
        self.termostats += [[tst_id, tst_val, trm_id, trm_val, title]]

    def _check_bmp280(self):
        try:
            res = self.bmp280_drv.get_data()
            t = res["t"]
            p = res["p"]

            for var in self.BMP280_VARS:
                if var[2] != res[var[1]]:
                    var[2] = res[var[1]]
                    self.db.IUD("call CORE_SET_VARIABLE(%s, %s, null)" % (var[0], var[2]))
                    self.db.commit()
        except Exception as e:
            self.bmp280_init()
            print(e)
            
    def _add_command(self, command):
        print("[%s] %s" % (time.strftime("%d-%m-%Y %H:%M"), command))
        """
        if self.get_quiet_time() and alarm == False:
            try:
                command.index("speech(")
                command = "speech(\"\")"
            except:
                pass
        """
        self.db.IUD("insert into core_execute (COMMAND) values ('%s')" % command)
        self.db.commit()

    def get_quiet_time(self):
        for rec in self.db.select("select VALUE from core_variables where NAME = 'QUIET_TIME'"):
            if rec[0]:
                return True
        return False

    def _load_cam_alerts(self):
        self.cam_alerts = []
        for rec in self.db.select("select NAME, ALERT_VAR_ID from plan_video order by ORDER_NUM"):
            self.cam_alerts += [rec[1]]

    def _clear_mem_db_table(self, table, space=100):
        for rec in self.db.select("select MAX(ID) from %s" % (table)):
            if rec[0]:
                max_id = rec[0] - space
                self.db.IUD("delete from %s where ID < %s" % (table, max_id))
                self.db.commit()
    
    def clear_mem_db(self):
        try:
            self._clear_mem_db_table("app_control_exe_queue")
            self._clear_mem_db_table("app_control_queue")
            self._clear_mem_db_table("app_control_sess")
            self._clear_mem_db_table("core_execute")
            self._clear_mem_db_table("core_variable_changes_mem")
            print("[%s] CLEAR MEM TABLES" % (time.strftime("%d-%m-%Y %H:%M")))
        except Exception as e:
            print(e)

    def clear_values(self):
        try:
            subprocess.call('python3 /home/pyhome/server/watcher/clear_values_15.py', shell=True)
        except:
            pass        

print(
"=============================================================================\n"
"               МОДУЛЬ НАБЛЮДЕНИЯ ЗА СОСТОЯНИЕМ СИСТЕМОЙ v0.1\n"
"\n"
"=============================================================================\n"
)

if __name__ == "__main__":    
    Main()
