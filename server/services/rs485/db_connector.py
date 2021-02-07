
import mysql.connector
import time
from datetime import datetime

class DBConnector(object):
    MYSQL_DB_NAME = "wisehouse"
    MYSQL_USER = "wisehouse"
    MYSQL_PASS = "wisehousepass"
    
    def __init__(self):
        self.mysqlConn = mysql.connector.connect(host="localhost",
                                                 database=self.MYSQL_DB_NAME,
                                                 user=self.MYSQL_USER,
                                                 password=self.MYSQL_PASS)
        
        self.query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED")
        
        self.lastVarChangeID = self._last_var_change_id()
        self.load_controllers()

    def query(self, sql, vars = []):
        q = self.mysqlConn.cursor()
        q.execute(sql, vars)
        return q

    def commit(self):
        self.mysqlConn.commit()

    def select(self, sql, vars = []):
        res = []        
        q = self.query(sql, vars)
        try:
            row = q.fetchone()
            while row:
                res += [row]
                row = q.fetchone()
        except:
            pass
        q.close()
        return res

    def IUD(self, sql, vars = []):
        q = self.query(sql, vars)
        q.close()        

    def get_property(self, name):        
        data = self.select("select VALUE from core_propertys where NAME='%s'" % name)
        if data:
            return str(data[0][0], "utf-8")
        else:
            return ''

    def set_property(self, name, value):
        self.IUD("update core_propertys set VALUE = '%s' where NAME = '%s'" % (value, name))
        self.commit()        

    def load_controllers(self):
        self.controllers = []
        q = self.query("select ID, NAME from core_controllers where ID < 100 order by ID desc")
        row = q.fetchone()
        while row is not None:
            nr = []
            for r in row:
                if type(r) == bytes:
                    r = str(r, "utf-8")
                nr += [r]
            self.controllers += [nr]
            row = q.fetchone()
        q.close()
        
    def _last_var_change_id(self):
        q = self.query("select MAX(ID) from core_variable_changes")
        row = q.fetchone()
        q.close()
        return row[0]

    def variable_changes(self):
        res = []
        for r in self.select("select CORE_GET_LAST_CHANGE_ID()"):
            if r[0] > self.lastVarChangeID + 1:
                q = self.query(("select ID, VARIABLE_ID, VALUE, FROM_ID"
                                "  from core_variable_changes_mem "
                                " where ID > %s "
                                "order by ID"), [self.lastVarChangeID])
                row = q.fetchone()

                while row is not None:
                    res += [[row[1], row[2], row[3]]]
                    self.lastVarChangeID = row[0]
                    row = q.fetchone()
                q.close()
        return res

    def all_variables(self):
        q = self.query(("select ID, VALUE"
                        "  from core_variables "
                        "order by ID"))
        row = q.fetchone()
        res = []
        while row is not None:
            res += [[row[0], row[1]]]
            row = q.fetchone()
        q.close()
        return res    

    def set_variable_value(self, var_id, var_value, dev_id):
        if dev_id == False:
            dev_id = "null"
            
        try:
            var_v = float(var_value)
            self.IUD("call CORE_SET_VARIABLE(%s, %s, %s)" % (var_id, var_v, dev_id))
            self.commit()
        except:
            print("Ошибка записи переменной в БД. ID: %s   VALUE: %s   FROM_ID: %s" % (var_id, var_v, dev_id))

    def append_scan_rom(self, dev_id, rom):
        data = [dev_id] + rom
        q = self.query("select count(*)"
                       "  from core_ow_devs"
                       " where CONTROLLER_ID = %s"
                       "   and ROM_1 = %s"
                       "   and ROM_2 = %s"
                       "   and ROM_3 = %s"
                       "   and ROM_4 = %s"
                       "   and ROM_5 = %s"
                       "   and ROM_6 = %s"
                       "   and ROM_7 = %s"
                       "   and ROM_8 = %s", data)
        row = q.fetchone()
        q.close()
        if row[0] == 0:
            q = self.query("insert into core_ow_devs "
                           "(CONTROLLER_ID, ROM_1, ROM_2, ROM_3, ROM_4, ROM_5, ROM_6, ROM_7, ROM_8)"
                           " values "
                           "(%s, %s, %s, %s, %s, %s, %s, %s, %s)", data)
            self.commit()
            q.close()
            return True        
        return False
