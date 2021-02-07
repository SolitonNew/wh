#!/usr/bin/python3
#-*- coding: utf-8 -*-

import socket
import threading
import time
import json
from db_connector import DBConnector

class VideoAlerts:
    def __init__(self, host, port):
        self.camIds = [166, 167, 168, 169]
        self.host = host
        self.port = port
        self.sessionID = ""
        self.bs = bytearray()
        self.bbs = bytearray(4)
        self.db = DBConnector()

    def run(self):
        while True:
            try:
                self.sock = socket.socket()
                self.sock.connect((self.host, self.port))
                self._send_login()
                self._send_empty()
                i = 0
                while True:
                    res = self.sock.recv(10240)
                    if res != b'':
                        res = res[20::].decode("cp1251")
                        for line in res.split('\n'):
                            try:
                                a = json.loads(line)
                                if a['Name'] == "AlarmInfo":
                                    c = a['AlarmInfo']
                                    if c['Event'] == 'VideoMotion':
                                        var_v = 0
                                        if c['Status'] == 'Start':
                                            var_v = 1
                                        cam = c['Channel']
                                        var_id = self.camIds[cam]
                                        self._print("Замечено движение на камере %s (статус %s)" % (cam + 1, var_v))
                                        self.db.IUD("call CORE_SET_VARIABLE(%s, %s, null)" % (var_id, var_v))
                                        self.db.commit()
                            except Exception as e:
                                #print("{}".format(e))
                                pass

                        if i >= 5:
                            i = -1
                            self._send_empty()
                        i += 1
                    else:
                        break
                    time.sleep(0.2)
            except Exception as e:
                print("{}".format(e))

            time.sleep(1)
            
            try:
                self.sock.close()
            except:
                print('error')

    def _send_pack(self, data):
        s = b'\xff' + self.bbs + data + b'\n'
        self.sock.sendall(s)
        #print("CLIENT: ", s)
        res = self.sock.recv(10240)
        #print("SERVER: ", res)
        return res

    def _send_login(self):
        data = b'\x00\x00\x00\x00\x00\x00\x00\x00\x00\xe8\x03d\x00\x00\x00{ "EncryptType" : "MD5", "LoginType" : "DVRIP-Web", "PassWord" : "tlJwpbo6", "UserName" : "admin" }'
        res = self._send_pack(data)
        res = res[20::].decode("cp1251").replace("\n", "").replace(chr(0), "")
        a = json.loads(res)
        self.sessionID = a['SessionID']
        self.bs = bytearray(self.sessionID, "cp1251")
        self.bbs = self._sessIdtoHex(self.sessionID)        

    def _send_empty(self):
        data = b'\x00\x00\x00\x00\x00\x00\x00\x00\x00\xdc\x05,\x00\x00\x00{ "Name" : "", "SessionID" : "' + self.bs + b'" }'
        self._send_pack(data)
        #print("PING")

    def _decodeHex(self, s):
        if s in '0123456789':
            return int(s)
        elif s == 'a':
            return 10
        elif s == 'b':
            return 11
        elif s == 'c':
            return 12
        elif s == 'd':
            return 13
        elif s == 'e':
            return 14
        else:
            return 15
    

    def _sessIdtoHex(self, s):
        res = bytearray(4)
        s = s[2::]
        for i in range(4):
            b1 = s[i * 2]
            b2 = s[i * 2 + 1]
            res[i] = (self._decodeHex(b1.lower()) << 4) + self._decodeHex(b2.lower())
        return res

    def _print(self, text):
        print("[%s] %s" % (time.strftime("%d-%m-%Y %H:%M:%S"), text))

host = "192.168.40.3"
port = 34567

print(
"=============================================================================\n"
"                 МОДУЛЬ ИНТЕГРАЦИИ С ВИДЕОРЕГИСТРАТОРОМ v0.1\n"
"\n"
" Хост: %s \n"
" Порт: %s \n"
"=============================================================================\n"
% (host, port)
)

if __name__ == "__main__":
    va = VideoAlerts(host, port)
    va.run()
