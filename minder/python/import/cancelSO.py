#!/usr/bin/env python2
"""
<title>
importfile.py, Version 20.02.06
</title>
<long>
Creates/Updates tables in the database
<br>
Parameters: <tt>input file</tt></tt>log file</tt>
the input file holds <tt>wh_id</tt><tt>location</tt>
<br>
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
	print "cancelHeldSO ", sys.argv[1]
	logfile = sys.argv[1]
	print "log ",logfile
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

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

if os.name == 'nt':
	con = kinterbasdb.connect(
		dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
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

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print "about to start select " + wk_date
#need status, locn
wk_select_stmt = "select pick_order from pick_item where pick_line_status='PL' group by pick_order "
print wk_select_stmt
cur.execute(wk_select_stmt )
data_fields = cur.fetchone()
if data_fields is None:
	print "No Picked Lines - all are ok"

while not data_fields is None:
	for pos in range(len(data_fields)):
		if  data_fields[pos] is None:
			wk_order = ""
		else:
			wk_order = data_fields[pos]
			wk_held_status = "HD"
			if wk_held_status == 'HD':
				# add tran
				cur2.callproc("cancel_sale_order",(
					wk_order, 
					"WRONG ORDER"))
	data_fields = cur.fetchone()

print "end - of cancel"
wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

con.commit()

wk_date = time.strftime("%d/%m/%y %H:%M:%S")
print wk_date

#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
