from db_connector import DBConnector
from datetime import datetime

db = DBConnector()

date_from = datetime.now().timestamp() // 1 - 3600 * 24 * 15

all_count = 0
for rec in db.select("select COUNT(*) "
                     "  from core_variable_changes c, core_variables v "
                     " where c.VARIABLE_ID = v.ID "
                     "   and v.APP_CONTROL in (4, 13, 14, 11)"
                     "   and UNIX_TIMESTAMP(c.CHANGE_DATE) > %s" % (date_from)):
    all_count = rec[0]

print("ВСЕГО ЗАПИСЕЙ: %s" % (all_count))

prev_var_id = -1
prev_var_time = None
prev_var_value = None

exit_check = False
values = []

def check_values(values, new_value):
    if len(values) > 1:
        row = values[-2]
        if row[1] == new_value[1]:
            return True
        else:
            return False
    else:
        return True

i = 0
curr_count = 0
for row in db.select("select c.ID, c.VALUE, c.VARIABLE_ID, UNIX_TIMESTAMP(c.CHANGE_DATE) "
                     "  from core_variable_changes c, core_variables v "
                     " where c.VARIABLE_ID = v.ID "
                     "   and v.APP_CONTROL in (4, 13, 14, 11)"
                     "   and UNIX_TIMESTAMP(c.CHANGE_DATE) > %s"
                     " order by c.VARIABLE_ID, c.CHANGE_DATE " % (date_from)):
    
    if row[2] == prev_var_id:
        if row[3] - prev_var_time < 600 and check_values(values, row): # Если часто, то вкидываем на просмотр
            values += [row]
        else:
            if len(values) > 2:
                f1, f2 = values[:2]
                l1, l2 = values[-2:]

                drop_ids = []
                for r in values:
                    if r[0] != f2[0] and r[0] != l1[0]:
                        drop_ids += [str(r[0]), ","]

                d = "".join(drop_ids[:-1])
                db.IUD("delete from core_variable_changes where ID in (%s)" % (d))

                v_f, v_l = f1[1] + (f2[1] - f1[1]) / 2, l1[1] + (l2[1] - l1[1]) / 2
                db.IUD("update core_variable_changes set VALUE = %s where ID = %s" % (v_f, f2[0]))
                db.IUD("update core_variable_changes set VALUE = %s where ID = %s" % (v_l, l1[0]))
                db.commit()
            
            values = [row]

        prev_var_time = row[3]
        prev_var_value = row[1]
    else:
        prev_var_id = row[2]
        prev_var_time = row[3]
        prev_var_value = row[1]
        values = []

    curr_count += 1
    i += 1
    if i >= 1000:
        i = 0
        print("%s / %s" % (curr_count, all_count))
        
print("%s / %s" % (curr_count, all_count))
new_count = 0
for rec in db.select("select COUNT(*) "
                     "  from core_variable_changes c, core_variables v "
                     " where c.VARIABLE_ID = v.ID "
                     "   and v.APP_CONTROL in (4, 13, 14, 11)"
                     "   and UNIX_TIMESTAMP(c.CHANGE_DATE) > %s" % (date_from)):
    new_count = rec[0]

print("ГОТОВО. БЫЛО / СТАЛО: %s / %s" % (all_count, new_count))
