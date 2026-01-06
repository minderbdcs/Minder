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

import kinterbasdb;kinterbasdb.init(type_conv=200)
#import kinterbasdb
#import mx.DateTime
import datetime

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
	#	dsn="127.0.0.1:d:/asset.rf/database/wh.v39.gdb",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:minder",
		user="sysdba",
		password="masterkey")
else:
	#		dsn="127.0.0.1:minder",
	con = kinterbasdb.connect(
		dsn="127.0.0.1:ft",
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
		buffer2 = line.split(',')
		wk_end = len(buffer2) -1
		# 	field1 is Company
		# 	field2  Transaction Date = PO_DATE
		# 	field3  PO
		# 	field4  Prod ID
		# 	field5  Due Date
		# 	field6  Qty
		# 	field7  Supplier
		# 	field8  First Name  
		# 	field9  Comments
		# 	fixed field10	Line_Status
		#		fixed field11   PO Status
		# 		fixed field12	Person Type
		# field1 is profit center description = Cost Center
		# field2  WBS = PO.PO_LEGACY_MEMO
		# field3  PO
		# field4  PO item # = PO Line
		# field5  Material = Prod ID
		# field6  Short Text = Prod_Profile.Short_Desc
		# field7  Vendor Name = Supplier Name !!! Not ID
		# field8  Warehouse = PO.Receive_WH_Name  
		# field9  Qty
		# field10  Created on Date = PO.PO_Legacy_Date
		# field11  Due Date - in po_date and po_due_date
		# field12  Received Date - may be empty
		# field13  Received Qty - may be empty
		# 	fixed field14	Line_Status
		#	fixed field15   PO Status
		# 	fixed field16	Person Type
		#
		#	default created prod_profile UOM = 'Each' ISSUE_UOM = 1 

		#print "wk_end",wk_end
		if wk_end < 1:
			break
		buffer2[wk_end] = buffer2[wk_end][:-1]
		buffer = []
		wk_inq = False
		for xindex in range(0,len(buffer2)):
			wk_str = buffer2[xindex]
			# take out quoted chars
			if wk_str.find("'") > -1:
				wk_str = wk_str.replace("'","`")
			if wk_str[:1] == '"':
				wk_inq = True
				wk_save_str = wk_str
				wk_save_start = xindex
			if wk_inq:
				if xindex > wk_save_start:
					wk_save_str = wk_save_str + wk_str		
				if wk_str[-1:] == '"':
					wk_inq = False
					buffer.append( wk_save_str[1:-1])
			else:
				buffer.append( wk_str)
		for xindex in range(0,len(buffer)):
			wk_str = buffer[xindex]
			print "x",xindex,buffer[xindex]
		#print "buffer size %d" % (len(buffer))
		buffer[13] = "OP" # line status
		buffer[14] = "OP" # order status
		buffer[15] = "CS" # person type
		buffer[16] = buffer[1] # memo comment
		wkk = buffer[9].split('/')
		#wk = "%2.2s-%2.2s-%2.2s" % (buffer[1][4:6],buffer[1][2:4],buffer[1][:2])
		print wkk
		#wk2 = mx.DateTime.DateTimeFrom(wk)
		wk2 = datetime.datetime(int(wkk[2]),int(wkk[0]),int(wkk[1]),0,0,0)
		print str(wk2)
		buffer[9] = wk2
		wkk = buffer[10].split('/')
		#wk = "%2.2s-%2.2s-%2.2s" % (buffer[4][4:6],buffer[4][2:4],buffer[4][:2])
		#print wk
		#wk2 = mx.DateTime.DateTimeFrom(wk)
		wk2 = datetime.datetime(int(wkk[2]),int(wkk[0]),int(wkk[1]),0,0,0)
		print str(wk2)
		buffer[10] = wk2
		buffer[17] = wk2
		buffer[18] = wk2
		wkk = buffer[11].split('/')
		if len(wkk) > 1:
			wk2 = datetime.datetime(int(wkk[2]),int(wkk[0]),int(wkk[1]),0,0,0)
			buffer[11] = wk2
			wk_use_recv_date = True
		else:
			wk_use_recv_date = False
		if len(buffer[12]) == 0:
			buffer[12] = 0
		buffer[19] = "WW" # company
		# calc select , update and insert statements
		if wk_use_recv_date:
			wk_insert_stmt = """insert into purchase_order_line_temp
 (H_COST_CENTER,
  H_PO_LEGACY_MEMO,
  H_PURCHASE_ORDER,
  L_PO_LINE,
  L_PROD_ID,
  PP_SHORT_DESC,
  P_FIRST_NAME,
  H_PO_LEGACY_RECEIVE_WH_NAME,
  L_PO_LINE_QTY,
  H_PO_LEGACY_DATE,
  H_PO_DATE,
  H_PO_LEGACY_RECV_DATE,
  H_PO_LEGACY_RECV_QTY,
  L_PO_LINE_STATUS,
  H_PO_STATUS,
  P_PERSON_TYPE,
  H_COMMENTS,
  H_PO_DUE_DATE,
  L_PO_LINE_DUE_DATE,
  H_COMPANY_ID)
 values ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')""" % (buffer[0],buffer[1],buffer[2],buffer[3],buffer[4],buffer[5],buffer[6],buffer[7],buffer[8],buffer[9],buffer[10],buffer[11],buffer[12],buffer[13],buffer[14],buffer[15],buffer[16],buffer[17],buffer[18],buffer[19])
		else:
			wk_insert_stmt = """insert into purchase_order_line_temp
 (H_COST_CENTER,
  H_PO_LEGACY_MEMO,
  H_PURCHASE_ORDER,
  L_PO_LINE,
  L_PROD_ID,
  PP_SHORT_DESC,
  P_FIRST_NAME,
  H_PO_LEGACY_RECEIVE_WH_NAME,
  L_PO_LINE_QTY,
  H_PO_LEGACY_DATE,
  H_PO_DATE,
  H_PO_LEGACY_RECV_QTY,
  L_PO_LINE_STATUS,
  H_PO_STATUS,
  P_PERSON_TYPE,
  H_COMMENTS,
  H_PO_DUE_DATE,
  L_PO_LINE_DUE_DATE,
  H_COMPANY_ID)
 values ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')""" % (buffer[0],buffer[1],buffer[2],buffer[3],buffer[4],buffer[5],buffer[6],buffer[7],buffer[8],buffer[9],buffer[10],buffer[12],buffer[13],buffer[14],buffer[15],buffer[16],buffer[17],buffer[18],buffer[19])
		print wk_insert_stmt	
		cur.execute(wk_insert_stmt )
			
con.commit()

print "end - of datafile"


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
