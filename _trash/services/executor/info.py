from datetime import datetime

class Info():
    def __init__(self):
        pass

    def check_comm(self, db, command):
        try:
            command.index("info()")

            d = datetime.now().hour - 1
            m = datetime.now().minute
            hours = ("Один час ночи",
                     "Два час*а ночи",
                     "Три час*а ночи",
                     "Четыре час*а ночи",
                     "Пять часов утр*а",
                     "Шесть часов утр*а",
                     "Семь часов утр*а",
                     "Восемь часов утр*а",
                     "Девять часов утр*а",
                     "Десять часов утр*а",
                     "Одинадцать часов утр*а",
                     "Двенадцать часов дня",
                     "Один час дня",
                     "Два час*а дня",
                     "Три час*а дня",
                     "Четыре час*а дня",
                     "Пять часов в*ечера",
                     "Шесть часов в*ечера",
                     "Семь часов в*ечера",
                     "Восемь часов в*ечера",
                     "Девять часов в*ечера",
                     "Десять часов в*ечера",
                     "Одинадцать часов ночи",
                     "Двенадцать часов ночи")

            minutes_2 = ("",
                         "одна",
                         "две",
                         "три",
                         "четыре",
                         "пять",
                         "шесть",
                         "семь",
                         "восемь",
                         "девять")

            minutes_1 = ("",
                         "",
                         "двадцать", 
                         "тридцать",
                         "сорок",
                         "пятдесят",
                         "шестьдесят")

            minutes = ("минут",
                       "минута",
                       "минуты",
                       "минуты",
                       "минуты",
                       "минут",
                       "минут",
                       "минут",
                       "минут",
                       "минут")            

            minute = ""
            if m > 0:
                minute = str(m)
                if m < 10:
                    minute = minutes_2[m] + " " + minutes[m]
                elif m < 20:
                    minute += " " + minutes[9]
                else:
                    try:
                        minute = minutes_1[int(minute[0])] + " " + minutes_2[int(minute[1])] + " " + minutes[int(minute[1])]
                    except Exception as e:
                        pass
                minute = ", %s" % minute

            #t_in = self._get_temp(db, "49,59", False)
            #t_in = self._get_temp(db, "59", False)
            t_out = self._get_temp(db, "124", True)
            
            #text = "%s %s. Температура по дому %s. Температура на улице %s." % (hours[d], minute, t_in, t_out)
            #text = "%s %s. Температура в гостинной %s. Температура на улице %s." % (hours[d], minute, t_in, t_out)
            text1 = "%s %s." % (hours[d], minute)
            text2 = "Температура на улице %s." % (t_out)

            print(text1, text2)

            db.IUD("insert into core_execute (COMMAND) values ('speech(\"%s\", \"notify\")')" % text1)
            db.IUD("insert into core_execute (COMMAND) values ('speech(\"%s\", \"notify\")')" % text2)
            db.commit()

            return True
        except:
            pass
        return False

    def _get_temp(self, db, ids, plus):
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
        v = 0
        c = 0
        t = datetime.now().timestamp() - 1800 * 2  #30 * 2 минут
        for row in db.select("select v.VALUE "
                             "  from core_variables v "
                             " where ID in (%s) "
                             "   and UNIX_TIMESTAMP(LAST_UPDATE) > %s"% (ids, t)):
            v += row[0]
            c += 1

        if c == 0:
            return "неизвестна"

        res = ""
        v = round(v / c)
        t_s = str(abs(int(v)))
        znak = ""
        if v < 0:
            znak = " мороза"
        elif v > 0 and plus:
            znak = " тепла"

        t_i = int(t_s[-1:])
        if abs(int(v)) > 9 and abs(int(v)) < 20:
            t_i = 0
        
        return "%s %s%s" % (t_s, temps[t_i], znak)

    def time_handler(self):
        pass
