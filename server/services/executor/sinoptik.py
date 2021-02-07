from datetime import datetime
import http.client
from urllib import parse

class Sinoptik():
    def __init__(self):
        pass

    def parseTag(self, content, tag1, tag2):
        try:
            content = content.replace("<!--noindex-->", "")
            content = content.replace("<!--/noindex-->", "")
            i1 = content.index(tag1) + len(tag1)
            i2 = content[i1:].index(tag2)
            s = content[i1:i1 + i2].strip()
            return s
        except:
            return ''

    def parceDescr(self, content):
        return self.parseTag(content, "<div class=\"description\">", "</div>")

    def parceTemp(self, content):
        t1 = self.parseTag(content, "<div class=\"min\">", "</div>")
        t1 = self.parseTag(t1, "<span>", "</span>")
        t1 = t1.replace("&deg;", "")
        
        t2 = self.parseTag(content, "<div class=\"max\">", "</div>")
        t2 = self.parseTag(t2, "<span>", "</span>")
        t2 = t2.replace("&deg;", "")
        
        return (t1, t2)

    def parceWarning(self, content):
        return self.parseTag(content, "class='tooltip'>", "</span>")

    def parceStorm(self, content):
        s1 = self.parseTag(content, "ico-stormWarning-1\">", "</div>")
        s2 = self.parseTag(content, "ico-stormWarning-2\">", "</div>")
        s3 = self.parseTag(content, "ico-stormWarning-3\">", "</div>")
        s4 = self.parseTag(content, "ico-stormWarning-4\">", "</div>")
        res = ". "
        if len(s1 + s2 + s3 + s4) > 0:
            res += "Ожидается "
        if len(s1) > 0:
            res += "%s. " % s1
        if len(s2) > 0:
            res += "%s. " % s2
        if len(s3) > 0:
            res += "%s. " % s3
        if len(s4) > 0:
            res += "%s. " % s4

        res = res.replace("мм", "милим*етров")
        res = res.replace("м/с", "метров за секунду")
        
        return res

    def check_comm(self, db, command):
        temps = ("градусов",
                 "градус",
                 "градуса", 
                 "градуса",
                 "градуса",
                 "градусов",
                 "градусов",
                 "градусов",
                 "градусов",
                 "градусов")
        
        try:
            command.index("sinoptik()")

            conn = http.client.HTTPSConnection("sinoptik.ua")
            conn.request("GET", "/" + parse.quote_plus("погода-долина-303007327"))
            resp = conn.getresponse()
            print(resp.status)
            content = resp.read().decode("utf-8")
            conn.close()

            f = open("sinoptik.txt", "w+")
            f.write(content)
            f.close()            

            text = "Прогноз погоды. " + self.parceDescr(content)
            w = self.parceWarning(content)
            if w != "":
                w = w.replace("c", " градуса")                
                text += " " + w + "."
            t = self.parceTemp(content)
            tt = int(t[1][-1:])
            text = text + " Температура воздуха %s %s %s." % (t[0], t[1], temps[tt])
            text = text.replace("вечера", "в*ечера")
            text = text.replace("утра", "утр*а")
            text = text.replace("аморозки", "*аморозки")

            text += self.parceStorm(content)
            
            db.IUD("insert into core_execute (COMMAND) values ('speech(\"%s\", \"notify\")')" % text)
            db.commit()
            
            return True
        except Exception as e:
            print("{}".format(e.args))
        return False

    def time_handler(self):
        pass
