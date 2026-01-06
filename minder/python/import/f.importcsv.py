#!/usr/bin/env python2
"""
<title>
importfile.py, Version 16.06.04
</title>
<long>
Creates/Updates tables in the database
<br>
Parameters: <tt>input file</tt></tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
The input filename minus the extension is the dataset to work on
The first record holds the columns to insert/update
Any field names starting with a '*' mean that these fields together
make up the unique key for the dataset
So check if the unique record exists
If not then insert it
else update it
<br>
</long>
"""
import sys
import string
import time , os ,glob
import fileinput

import kinterbasdb
import mx.DateTime

#redirect stdout and stderr

if len(sys.argv)>0:
	print "importfile ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importfile stdin"
	infile = '-'
	havein = 0;

if len(sys.argv)>1:
	print "log ", sys.argv[2]
	logfile = sys.argv[2]
	havelog = 1;
else:
	print "log stdin"
	havelog = 0;

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'a')
	sys.stdout = out
	sys.stderr = out

rest, ext = os.path.splitext(infile)
path, base = os.path.split(rest)
print "%s %s" % ("base",base)
if base.rfind("(") > -1:
	path2 = base[:base.rfind("(") ]
	base = path2
wk_dataset = base

print "%s %s" % ("dataset",wk_dataset)

if os.name == 'nt':
	con = kinterbasdb.connect(
                dsn="203.7.14.45/3051:bhp2",
                user="sysdba",
		password="masterkey")
else:
	con = kinterbasdb.connect(
		dsn="/data/asset.rf/wh.v39.gdb",
		user="sysdba",
		password="masterkey")

print "connected to db"
cur = con.cursor()
cur2 = con.cursor()
cur3 = con.cursor()

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	if wk_line == 1:
		keys = []
		keys_no = []
		fields_nokey = []
		fields_nokey_no = []
		fields = line.split(',')
		wk_end = len(fields) -1
		print "wk_end",wk_end
		if wk_end < 1:
			break
                #fields[wk_end] = fields[wk_end][:-1]
		wk_insert = "insert into %s (" % (wk_dataset )
		wk_insert_sfx = ""
		wk_select = "select first 1 1 from %s " % (wk_dataset )
		wk_update = "update %s set " % (wk_dataset )
		wk_where = "where "
		for xindex in range(0,len(fields)):
			wk_str = fields[xindex]
			if wk_str[:1] == '"':
                                if wk_str[-1:] == '"':
                                        fields[xindex] = wk_str[1:-1]
                                else:
                                        wk_str = wk_str.strip()
                                        if wk_str[-1:] == '"':
                                                fields[xindex] = wk_str[1:-1]
			wk_str = fields[xindex]
			if wk_str[:1] == "*":
				print "key"
				keys.append(wk_str[1:])
				keys_no.append(xindex)
				fields[xindex] = wk_str[1:]
                                #wk_where += "%s %s and " % ( wk_str[1:], " = '%s'")
                                wk_where += "%s %s and " % ( wk_str[1:], " = ? ")
			else :
				fields_nokey.append(wk_str)
				fields_nokey_no.append(xindex)
                                #wk_update += "%s %s , " % ( wk_str, " = '%s'")
                                wk_update += "%s %s , " % ( wk_str, " = ?")
			print xindex,fields[xindex]
			wk_insert += "%s ," % (fields[xindex])
                        #wk_insert_sfx += "'%s' ,"
                        wk_insert_sfx += "? ,"
		print "keys", str(keys)
		print "keys_no", str(keys_no)
		print "fields_nokey", str(fields_nokey)
		print "fields_nokey_no", str(fields_nokey_no)
		# calc where clause for select and update
		# then calc select statement
		if wk_where == "where ":
			wk_select = ""
		else:	
			wk_select += wk_where[:-4] 
		#print wk_select	
		wk_select_prep = cur.prep(wk_select)
		# insert statement
		wk_insert = wk_insert[:-1] + ") values (" + wk_insert_sfx[:-1] + ")"
		#print wk_insert	
		wk_insert_prep = cur3.prep(wk_insert)
		# update statement
		wk_update = wk_update[:-2] + wk_where[:-4] 
		#print wk_update	
		wk_update_prep = cur2.prep(wk_update)
	else:
		buffer = line.split(',')
		wk_end = len(buffer) -1
		#print "wk_end",wk_end
		if wk_end < 1:
			break
                #buffer[wk_end] = buffer[wk_end][:-1]
		for xindex in range(0,len(buffer)):
			wk_str = buffer[xindex]
			if wk_str[:1] == '"':
				buffer[xindex] = wk_str[1:-1]
			wk_str = buffer[xindex]
			print "x",xindex,buffer[xindex]
		# calc select , update and insert statements
		wk_keys_data = []
		wk_nokeys_data = []
		for windex in range(0,len(keys_no)):
			wk_keys_data.append(buffer[keys_no[windex]])
		for windex in range(0,len(fields_nokey_no)):
			wk_nokeys_data.append(buffer[fields_nokey_no[windex]])
                #wk_select_stmt = wk_select % tuple(wk_keys_data)
                #wk_select_stmt = wk_select
                wk_select_stmt = wk_select_prep
		print wk_select_stmt	
		if len(wk_nokeys_data) > 0:
                        #wk_update_stmt = wk_update % tuple(wk_nokeys_data + wk_keys_data)
                        #wk_update_stmt = wk_update
                        wk_update_stmt = wk_update_prep
		else:
			wk_update_stmt = ""
		#print wk_update_stmt	
                #wk_insert_stmt = wk_insert % tuple(buffer)
                #wk_insert_stmt = wk_insert
                wk_insert_stmt = wk_insert_prep
		#print wk_insert_stmt	


		# perform select
		# if record found do the update
		# else do the insert
	
		if wk_select_stmt > "":			
                        #cur.execute(wk_select_stmt)
                        cur.execute(wk_select_stmt, tuple(wk_keys_data))
			
			## get data record
			data_fields = cur.fetchone()
		else:
			data_fields = None
			
		#print data_fields
		if data_fields is None:
			print "record not found "
			record_found = None
		else:
			print "record found "
			#print data_fields
			record_found = data_fields[0]
		if record_found == 1 :
			print wk_update_stmt	
			query4 = wk_update_stmt
                        if query4 <> "":
                                cur2.execute(query4, tuple(wk_nokeys_data + wk_keys_data))
		else :
			print wk_insert_stmt	
			query4 = wk_insert_stmt
                        if query4 <> "":
                                cur3.execute(query4, tuple(buffer))        

                #print query4 
                #if query4 <> "":
                #        cur.execute(query4 )
			
con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
