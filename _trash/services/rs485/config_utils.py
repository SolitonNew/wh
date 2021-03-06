from db_connector import DBConnector

def get_ow_roms(db):
    lst = []
    sql = "select ID, ROM_1, ROM_2, ROM_3, ROM_4, ROM_5, ROM_6, ROM_7, ROM_8 from core_ow_devs"
    q = db.query(sql)
    row = q.fetchone()    
    while row is not None:
        res = ["["]
        for b in range(1, len(row)):
            s = hex(row[b])
            if len(s) == 3:
                s = s.replace("0x", "0x0")
            res += [s, ', ']

        del res[len(res) - 1]
        res += ["]"]
        lst += [[row[0], "".join(res)]]
        row = q.fetchone()
    q.close()
    return lst


def generate_variable_list(db):
    res = []
    
    roms = get_ow_roms(db)
    
    templ = "%s = Variable(%d, %d, %d, %s, '%s')"
    for row in db.select("select NAME, ID, CONTROLLER_ID, DIRECTION, ROM, CHANNEL, OW_ID from core_variables order by ID"):
        name = row[0].decode("utf-8")
        rom = row[4].decode("utf-8")
        if rom == "ow":
            rom = "''";
            for rrr in roms:
                if rrr[0] == row[6]:
                    rom = rrr[1]
        else:
            rom = "'" + rom + "'"
        channel = ''
        if row[5]:
            channel = row[5].decode("utf-8")
            if channel == "TEMP":
                channel = ""
        if name == '':
            name = "VAR_%s" % (row[1])
          
        s = templ % (name, row[1], row[2], row[3], rom, channel)
        res += [s, "\n"]
    return res

def generate_script_list(db):
    tab = "    "
    res = ["# Scripts\n"]
    for row in db.select("select ID, COMM, DATA from core_scripts order by ID"):
        res += ["def script_", str(row[0]), "():\n"]
        s = tab + str(row[2], "utf-8")
        s = s.replace(chr(10), "\n" + tab)
        s = s.replace(chr(13), "")
        res += [s]
        res += ["\n\n"]
    return res

def generate_var_2_script_list(db):
    res = ["# Links\n"]
    for row in db.select("select v.NAME, s.ID, e.EVENT_TYPE"
                         "  from core_variables v, core_scripts s, core_variable_events e"
                         " where e.VARIABLE_ID = v.ID"
                         "   and e.SCRIPT_ID = s.ID"):
        if row[2] == 0:
            res += "%s.set_change_script(script_%s)\n" % (str(row[0], "utf-8"), row[1])

    return res

def generate_config_file(db):
    res = ["from variables import Variable \n\n"]    
    res += ["# Variables\n"]
    res += generate_variable_list(db)
    res += ["\n"]
    res += generate_script_list(db)
    res += ["\n"]    
    res += generate_var_2_script_list(db)
    return "".join(res)


#print(generate_config_file(DBConnector()))