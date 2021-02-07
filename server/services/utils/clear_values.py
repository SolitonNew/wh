from db_connector import DBConnector

db = DBConnector()

all_count = 0
for rec in db.select("select COUNT(*) from core_variable_changes"):
    all_count = rec[0]

print("ВСЕГО ЗАПИСЕЙ: %s" % (all_count))

db.query("update core_variable_changes"
         "   set value = ROUND(value * 10) / 10"
         " where VARIABLE_ID = 153 ")
db.commit()

prev_var_id = -1
prev_var_value = None

i = 0
curr_count = 0
for row in db.select("select ID, VALUE, VARIABLE_ID "
                     "  from core_variable_changes "
                     " where VARIABLE_ID = 153 "
                     " order by VARIABLE_ID, CHANGE_DATE "):
    if row[2] == prev_var_id:
        if row[1] == prev_var_value:
            db.query("delete from core_variable_changes where ID = %s" % (row[0]))
            db.commit()
        else:
            prev_var_value = row[1]
    else:
        prev_var_id = row[2]
        prev_var_value = row[1]

    curr_count += 1
    i += 1
    if i >= 5000:
        i = 0
        print("%s / %s" % (curr_count, all_count))
        
print("%s / %s" % (curr_count, all_count))
new_count = 0
for rec in db.select("select COUNT(*) from core_variable_changes"):
    new_count = rec[0]

print("ГОТОВО. БЫЛО / СТАЛО: %s / %s" % (all_count, new_count))
