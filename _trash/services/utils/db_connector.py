import mysql.connector

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
        
        #self.query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED")

    def disconnect(self):
        if self.mysqlConn:
            self.mysqlConn.disconnect()
        
    def query(self, sql, vars = []):
        q = self.mysqlConn.cursor()
        q.execute(sql, vars)
        return q

    def commit(self):
        self.mysqlConn.commit()

    def rollback(self):
        self.mysqlConn.rollback()

    def select(self, sql, vars = []):
        res = []        
        q = self.query(sql, vars)
        try:
            res = q.fetchall()
        except:
            pass
        q.close()
        return res

    def IUD(self, sql, vars = []):
        q = self.query(sql, vars)
        self._lastID = q.lastrowid
        q.close()

    def lastID(self):
        return self._lastID

    def get_property(self, name):        
        data = self.select("select VALUE from core_propertys where NAME='%s'" % name)
        if data:
            return str(data[0][0], "utf-8")
        else:
            return ''

    def set_property(self, name, value):
        self.IUD("update core_propertys set VALUE = '%s' where NAME = '%s'" % (value, name))
        self.commit()
