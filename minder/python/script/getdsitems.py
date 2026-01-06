#!/usr/bin/env python2
"""
<title>
getdsitems.py, Version 10.04.05
</title>
<long>
despatches any ds status pick_items 
<br>
Parameters: <tt>log file</tt>
<br>
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

if os.name == 'nt':
	logfile = "d:/tmp/getdsitems.log"
else:
	logfile = "/tmp/getdsitems.log"
havelog = 1;
#print "getreruns log ", logfile

#
#redirect stdout and stderr
if (havelog == 1):
	out = open(logfile,'w')
	sys.stdout = out
	sys.stderr = out

if os.name == 'nt':
	#	dsn="127.0.0.1:d:/asset.rf/database/bss2.gdb",
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
tran_type = "GCPD"
tran_class = "S"
tran_delim = "|"
tran_user = "BDCS"
tran_device = "XX"
tran_source = "SSS"
#print "trans type",tran_type,"class",tran_class
#print "user",tran_user,"dev",tran_device,"source",tran_source

mytime = mx.DateTime.now()
#get defaults
query0 = """select default_carrier_id, default_despatch_printer, default_connote_weight, default_connote_qty_labels, default_connote_pack, default_connote_pack_qty from control""";
cur.execute(query0)
## get data record
data_fields1 = cur.fetchone()
#print data_fields
if data_fields1 is None:
	print "no Control "
else:
	while not data_fields1 is None:
		default_carrier = data_fields1[0]
		default_printer = data_fields1[1]
		default_weight = data_fields1[2]
		default_qty_labels = data_fields1[3]
		default_pack = data_fields1[4]
		default_pack_qty = data_fields1[5]
		data_fields1 = cur.fetchone()
query1 = "select default_connote_isso, trn_type from carrier where carrier_id = '%s'" % (default_carrier);
cur.execute(query1)
## get data record
data_fields1 = cur.fetchone()
#print data_fields
if data_fields1 is None:
	print "no Carrier "
else:
	while not data_fields1 is None:
		default_isso = data_fields1[0]
		tran_type_OT = data_fields1[1]
		data_fields1 = cur.fetchone()

query1 = "select service_type from carrier_service where carrier_id = '%s'" % (default_carrier);
cur.execute(query1)
## get data record
data_fields1 = cur.fetchone()
#print data_fields
if data_fields1 is None:
	print "no Carrier Service "
else:
	if not data_fields1 is None:
		default_service = data_fields1[0]

tran_user = "BDCS"
tran_device = "XD"
tran_instance = "MASTER    "

# Now get errored transactions
query3 = """select pick_order  from pick_item where pick_line_status='DS' group by pick_order """

cur2.execute(query3)

## get data record
data_fields = cur2.fetchone()
#print data_fields
if data_fields is None:
	print "no DS status Pick Items "
else:
	while not data_fields is None:
		#print data_fields
		#tran_type = data_fields[0]
		tran_type = tran_type_OT
		#tran_class = data_fields[1]
		tran_class = 'S'
		#tran_date = data_fields[2]
		tran_date = mx.DateTime.now()
		#tran_user = data_fields[3]
		#tran_device = data_fields[4]
		#tran_source = data_fields[5]
		tran_source = "SSOSSSSSS"
		#tran_instance = data_fields[6]
		#tran_wh = data_fields[7]
		#tran_locn = data_fields[8]
		#tran_object = data_fields[9]
		#tran_ref = data_fields[10]
		#tran_qty = data_fields[11]
		#tran_sublocn = data_fields[12]
		tran_sublocn = default_carrier
		wk_order = data_fields[0]
		tran_wh = wk_order[0:2]
		tran_locn = wk_order[2:]
		tran_object = "%-20.20s" % (wk_order)
		if default_pack == 'P':
			tran_ref = "%4.4d" % (default_pack_qty )
		else:
			tran_ref = "0000"
		tran_ref += "NONE      "
		if default_pack == 'C':
			tran_ref += "%4.4d" % (default_pack_qty) 
		else:
			tran_ref += "0000"
		if default_pack == 'S':
			tran_ref += "%4.4d" % (default_pack_qty) 
		else:
			tran_ref += "0000"
		tran_ref += "%5.5d" % (default_weight) 
		tran_ref += "%5.5d" % ( 0 )
		tran_ref += "SS" 
		tran_ref += "%3.3s" % (default_service) 
		tran_ref += "|%s" % (default_printer) 

		tran_qty = int(default_qty_labels)
		print "got %s " % (wk_order )
		print "trans type",tran_type,"class",tran_class
		print "item",tran_object,"locn",tran_locn
		print "sublocn",tran_sublocn,"ref",tran_ref,"qty",tran_qty
		print "user",tran_user,"dev",tran_device,"source",tran_source

		# then add transactions
		
		query4 = """select response_text from add_tran_response(
			'%s','%s','%s','%s','%s','%s','%s','%s','F','','MASTER    ',0,'%s','%s','%s','%s')
		"""
		
		cur.execute(query4 % (tran_wh, tran_locn, tran_object, tran_type, tran_class, tran_date, tran_ref, tran_qty, tran_sublocn, tran_source, tran_user, tran_device ))
		
		## get data record
		data_fields = cur.fetchone()
		#print data_fields
		if data_fields is None:
			print "no record id "
			record_id = ""
		else:
			#print data_fields
			record_id = data_fields[0]
		
		print "response %s" % record_id
		# if response is "" then must look at transactions table
		# to see whether worked or not	
		# ok so is rerun
		data_fields = cur2.fetchone()

print "end of reading transactions " 

con.commit()

print "end - of "


#revert stdin stdout and stderr
if (havelog == 1):
	sys.stdout = sys.__stdout__
	sys.stderr = sys.__stderr__
	out.close()

###
