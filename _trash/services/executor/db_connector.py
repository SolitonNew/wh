import mysql.connector
import time
from datetime import datetime

class DBConnector(object):
    MYSQL_DB_NAME = "wisehouse"
    MYSQL_USER = "wisehouse"
    MYSQL_PASS = "wisehousepass"
    
    def __init__(self):
        self._lastID = -1
        self.mysqlConn = mysql.connector.connect(host="localhost",
                                                 database=self.MYSQL_DB_NAME,
                                                 user=self.MYSQL_USER,
                                                 password=self.MYSQL_PASS)
        
        self.query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED")
        
        self.lastVarChangeID = self._last_var_change_id()

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
        self._lastID = q.lastrowid
        q.close()        
        
    def _last_var_change_id(self):
        q = self.query("select MAX(ID) from core_variable_changes")
        row = q.fetchone()
        q.close()
        return row[0]

    def variable_changes(self):
        res = []
        for row in self.select(("select c.ID, c.VARIABLE_ID, c.VALUE, v.APP_CONTROL, v.GROUP_ID"
                                "  from core_variable_changes c, core_variables v "
                                " where c.ID > %s "
                                "   and c.VARIABLE_ID = v.ID "
                                "order by c.ID"), [self.lastVarChangeID]):
            res += [row]
            self.lastVarChangeID = row[0]
        return res
