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
	print "importSOStatus ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importSOStatus stdin"
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

wk_date = time.strftime("%d/%m/%y")
wk_line = 0	
#read std or 1st input parm
for line in fileinput.input(infile):
	wk_line = wk_line + 1
	print "line",line
	#wk_code = line
	if wk_line == 1:
		print "1st line" 
	else:
		buffer = line.split(',')
		wk_end = len(buffer) -1
		# field1 is Tran Type
		# field2 is Tran Class
		# field3  Transaction Date yyyymmddhhmmss
		# field4  Order Prefix
		# field5  SO
		# field6  Company 
		# field7  Status 
		# field8  Prod ID
		# field9  Qty
		# field10  User
		# field11  Device  
		# field12  Customer PO WO

		#print "wk_end",wk_end
		if wk_end < 1:
			break
		buffer[wk_end] = buffer[wk_end][:-1]
		for xindex in range(0,len(buffer)):
			wk_str = buffer[xindex]
			if wk_str[:1] == '"':
				buffer[xindex] = wk_str[1:-1]
			print "x",xindex,buffer[xindex]
		#print "buffer size %d" % (len(buffer))
		#wk = "%2.2s-%2.2s-%4.4s %2.2s:%2.2s:%2.2s" % (buffer[2][6:6],buffer[2][4:8],buffer[2][:4],buffer[2][8:10],buffer[2][10:12],buffer[2][12:14])
		#print wk
		#wk2 = mx.DateTime.DateTimeFrom(wk)
		#print str(wk2)
		#buffer[2] = wk2
		wk_order = buffer[3] + buffer[4]
		wk_status = buffer[6]
		wk_select_stmt = "select pick_status from pick_order where pick_order = '%s' and pick_order_started is null" % (wk_order)
		wk_doit = "F"
		print wk_select_stmt
		cur.execute(wk_select_stmt )
		data_fields = cur.fetchone()
		if data_fields is None:
			wk_doit = "F"
		while not data_fields is None:
			for pos in range(len(data_fields)):
				if  data_fields[pos] is None:
					wk_doit = "F"
				else:
					wk_doit = "T"
		if wk_doit == "T":
			if wk_status == "HD":
				# suspend order
				wk_update_stmt = "update pick_order set pick_status='HD' where pick_order = '%s'" % (wk_order)
				print wk_update_stmt	
				cur.execute(wk_update_stmt )
			elif wk_status == "CN":
				# cancel order
				# do cancel_sale_order
				cur.callproc("cancel_sale_order",( wk_order, "Cancelled by Legacy System"))
		else:
			print "Cannot Cancel/Hold - Order already started"
con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
