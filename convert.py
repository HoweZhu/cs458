import sys
import os
from string import Template
from dbfread import DBF

# pip install dbfread
# ex: python convert.py 2015\accident.dbf > 2015_accident.sql

def esc_quotes(input):
    return input.replace('\'', '\'').replace('\"', '\"')

def parse_input(_input):
    # ex: List of columns
    if isinstance(_input, list):
        string = ""
        for i in range(len(_input)):
            column = _input[i]

            string += str(column)
            
            if i < len(_input) - 1: 
                string += ","

        #string = esc_quotes(string)
        return string

    # ex: k,v pairs from record
    elif isinstance(_input, dict):
        string = ""
        i = 0
        for col in _input:
            val = _input[col]
            values = ""
            
            if isinstance(val, str) or isinstance(val, unicode):
                values += "\"" + str(val) + "\""
            else:
                values += str(val)

            if i < len(_input) - 1: 
                values += ","

            string += values 
            i += 1

        string = esc_quotes(string)
        return string

    else:
        sys.stderr.write("Unsupported type:")
        sys.exit(1)

def main():
    # --- Check args 
    if len(sys.argv) != 2:
        sys.stderr.write("Usage: python convert.py <path-to-dbf-file>")
        sys.exit(1)
    elif os.path.exists(sys.argv[1]) != True:
        sys.stderr.write(sys.argv[1] + " does not exist.")
        sys.exit(1)
    elif sys.argv[1].find(".dbf") == -1:
        sys.stderr.write(sys.argv[1] + " is not a .dbf file.")
        sys.exit(1)
    
    dbf_path = sys.argv[1]
    table = DBF(dbf_path) # load=True loads into main memory

    # --- Init SQL stuff
    table_name = os.path.basename(dbf_path).split(".dbf")[0]
    columns = parse_input(table.field_names)
    sql_statements = []
    ins_template = Template("INSERT INTO $tbl_name ($tbl_cols) VALUES($tbl_vals);")

    # --- Prepare insert statements
    for record in table:
        values = parse_input(record)

        statement = ins_template.substitute(
            tbl_name=table_name, 
            tbl_cols=columns, 
            tbl_vals=values
        )
        sql_statements.append(statement)

    # --- Finalize sql script
    sql_statements.append("COMMIT;")
    output = ""
    i = 0
    for statement in sql_statements:
        if i < len(sql_statements) - 1: 
            output += str(statement) + '\n'
        else:
            output += str(statement)
        i += 1

    print output

if __name__ == "__main__":
    main()
