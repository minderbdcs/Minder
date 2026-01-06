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
	print "importPO ", sys.argv[1]
	infile = sys.argv[1]
	havein = 1;
else:
	print "importPO stdin"
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
		# field1 is Company
		# field2  Transaction Date = PO_DATE
		# field3  PO
		# field4  Prod ID
		# field5  Due Date
		# field6  Qty
		# field7  Supplier
		# field8  First Name  
		# field9  Comments
		# fixed field10	Line_Status
		#	fixed field11   PO Status
		# 	fixed field12	Person Type

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
		if len(buffer) == 9:
			# no status specified
			buffer.append("OP") # line status
		buffer.append("OP") # order status
		buffer.append("CS") # person type
		wk = "%2.2s-%2.2s-%2.2s" % (buffer[1][4:6],buffer[1][2:4],buffer[1][:2])
		print wk
		wk2 = mx.DateTime.DateTimeFrom(wk)
		print str(wk2)
		buffer[1] = wk2
		wk = "%2.2s-%2.2s-%2.2s" % (buffer[4][4:6],buffer[4][2:4],buffer[4][:2])
		print wk
		wk2 = mx.DateTime.DateTimeFrom(wk)
		print str(wk2)
		buffer[4] = wk2
		# calc select , update and insert statements
		wk_insert_stmt = "insert into purchase_order_line_temp (H_COMPANY_ID,H_PO_DATE,H_PURCHASE_ORDER,L_PROD_ID,L_PO_LINE_DUE_DATE,L_PO_LINE_QTY,H_PERSON_ID,P_FIRST_NAME,H_COMMENTS,L_PO_LINE_STATUS,H_PO_STATUS,P_PERSON_TYPE) values ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')" % tuple(buffer)
		print wk_insert_stmt	

		#print query4 
		cur.execute(wk_insert_stmt )
			
con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
